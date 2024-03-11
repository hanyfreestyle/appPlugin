<?php

namespace App\AppPlugin\Product;

use App\AppPlugin\Product\Models\Product;
use App\AppPlugin\Product\Models\ProductAttribute;
use App\AppPlugin\Product\Models\ProductAttributeTranslation;
use App\AppPlugin\Product\Request\ProductAttributeRequest;
use App\Helpers\AdminHelper;
use App\Http\Controllers\AdminMainController;
use App\Http\Traits\CrudTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class ShopAttributeController extends AdminMainController {
    use CrudTraits;

    function __construct(ProductAttribute $model) {
        parent::__construct();
        $this->controllerName = "ProAttribute";
        $this->PrefixRole = 'Product';
        $this->selMenu = "Shop.";
        $this->PrefixCatRoute = "";
        $this->PageTitle = __('admin/proProduct.app_menu_attribute');
        $this->PrefixRoute = $this->selMenu . $this->controllerName;
        $this->model = $model;

        $sendArr = [
            'TitlePage' => $this->PageTitle,
            'PrefixRoute' => $this->PrefixRoute,
            'PrefixRole' => $this->PrefixRole,
            'AddConfig' => true,
            'configArr' => ["filterid" => 0,"orderbyPostion"=>1],
            'yajraTable' => false,
            'AddLang' => false,
            'restore' => 0,
        ];

        self::loadConstructData($sendArr);

    }

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| # ClearCash
    public function ClearCash() {
        Cache::forget('CCCCCCCCCCCC');
    }

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| #     index
    public function index() {
        $pageData = $this->pageData;
        $pageData['ViewType'] = "List";
        $pageData['SubView'] = false;
        $rowData = self::getSelectQuery(ProductAttribute::def());
        return view('AppPlugin.Product.attribute_index', compact('pageData', 'rowData'));
    }

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| #     create
    public function create() {
        $pageData = $this->pageData;
        $pageData['ViewType'] = "Add";
        $rowData = ProductAttribute::findOrNew(0);
        return view('AppPlugin.Product.attribute_form')->with([
                'pageData' => $pageData,
                'rowData' => $rowData,
            ]
        );
    }
#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| #     edit
    public function edit($id) {
        $pageData = $this->pageData;
        $pageData['ViewType'] = "Edit";
        $rowData = ProductAttribute::where('id', $id)->firstOrFail();
        return view('AppPlugin.Product.attribute_form')->with([
                'pageData' => $pageData,
                'rowData' => $rowData,
            ]
        );
    }
#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| #     storeUpdate
    public function storeUpdate(ProductAttributeRequest $request, $id = 0) {
        $saveData = ProductAttribute::findOrNew($id);
        try {
            DB::transaction(function () use ($request, $saveData) {
                $saveData->is_active = intval((bool)$request->input('is_active'));
                $saveData->type = intval((bool)$request->input('type'));
                $saveData->save();
                foreach (config('app.web_lang') as $key => $lang) {
                    $saveTranslation = ProductAttributeTranslation::where('attribute_id', $saveData->id)->where('locale', $key)->firstOrNew();
                    $saveTranslation->locale = $key;
                    $saveTranslation->attribute_id = $saveData->id;
                    $saveTranslation->name = $request->input($key . '.name');
                    $saveTranslation->slug = AdminHelper::Url_Slug($request->input($key . '.name'));
                    $saveTranslation->save();
                }
            });
        } catch (\Exception $exception) {
            return back()->with('data_not_save', "");
        }
        self::ClearCash();
        return self::redirectWhere($request, $id, $this->PrefixRoute . '.index');
    }


#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| #     CategorySort
    public function Sort() {
        $pageData = $this->pageData;
        $pageData['ViewType'] = "List";
        $thisRow = null;
        $rowData = ProductAttribute::orderBy('postion')->get();
        return view('AppPlugin.Product.attribute_sort', compact('pageData', 'rowData', 'thisRow'));
    }

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| #     SaveSort
    public function SaveSort(Request $request) {
        $positions = $request->positions;
        foreach ($positions as $position) {
            $id = $position[0];
            $newPosition = $position[1];
            $saveData = ProductAttribute::findOrFail($id);
            $saveData->postion = $newPosition;
            $saveData->save();
        }
        self::ClearCash();
        return response()->json(['success' => $positions]);
    }

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| #     ForceDeletes
    public function ForceDeleteException($id) {

        $deleteRow = ProductAttribute::where('id', $id)->withcount('option')->firstOrFail();
        if($deleteRow->option_count == 0) {
            try {
                DB::transaction(function () use ($deleteRow, $id) {
                    $deleteRow->forceDelete();
                });
            } catch (\Exception $exception) {
                return back()->with(['confirmException' => '', 'fromModel' => 'Attribute', 'deleteRow' => $deleteRow]);
            }
        } else {
            return back()->with(['confirmException' => '', 'fromModel' => 'Attribute', 'deleteRow' => $deleteRow]);
        }

        self::ClearCash();
        return back()->with('confirmDelete', "");
    }

}
