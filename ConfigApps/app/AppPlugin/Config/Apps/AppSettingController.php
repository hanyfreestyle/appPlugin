<?php

namespace App\AppPlugin\Config\Apps;

use App\Helpers\photoUpload\PuzzleUploadProcess;
use App\Http\Controllers\AdminMainController;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class AppSettingController extends AdminMainController {

    function __construct() {

        parent::__construct();
        $this->controllerName = "AppSetting";
        $this->PrefixRole = 'AppSetting';
        $this->selMenu = "";
        $this->PrefixCatRoute = "";
        $this->PageTitle = __('admin/configApp.app_menu');
        $this->PrefixRoute = $this->selMenu . $this->controllerName;


        $sendArr = [
            'TitlePage' => $this->PageTitle,
            'PrefixRoute' => $this->PrefixRoute,
            'PrefixRole' => $this->PrefixRole,
            'AddButToCard' => false,
        ];
        self::loadConstructData($sendArr);
        $this->middleware('permission:' . $this->PrefixRole . '_edit', ['only' => ['AppSettingUpdate', 'photoUpdate', 'AppProfileUpdate']]);

    }

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| # ClearCash
    public function ClearCash() {
        Cache::forget('XXXXXXXXXXXXXXXX');
    }

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| #   webConfigEdit
    public function AppSetting() {
        $pageData = $this->pageData;
        $pageData['ViewType'] = "Edit";
        $setting = AppSetting::findOrFail(1);

        return view('AppPlugin.ConfigApp.setting')->with(compact('pageData', 'setting'));
    }

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| #
    public function AppSettingUpdate(AppSettingRequest $request) {

        $saveData = AppSetting::findorfail('1');
        $saveData->status = intval((bool)$request->input('status'));
        $saveData->baseUrl = $request->input('baseUrl');
        $saveData->mobilePrefix = $request->input('mobilePrefix');
        $saveData->prefix = $request->input('prefix');

        $saveData->ColorDark = $request->input('ColorDark');
        $saveData->ColorLight = $request->input('ColorLight');
        $saveData->AppBarIconColor = $request->input('AppBarIconColor');
        $saveData->BackGroundColor = $request->input('BackGroundColor');
        $saveData->SplashColor = $request->input('SplashColor');
        $saveData->PreloadingColor = $request->input('PreloadingColor');
        $saveData->StatueBArColor = $request->input('StatueBArColor');
        $saveData->AppBarColor = $request->input('AppBarColor');
        $saveData->AppBarColorText = $request->input('AppBarColorText');
        $saveData->sideMenuText = $request->input('sideMenuText');
        $saveData->sideMenuColor = $request->input('sideMenuColor');
        $saveData->mainScreenScale = $request->input('mainScreenScale');
        $saveData->sideMenuAngle = $request->input('sideMenuAngle');
        $saveData->sideMenuStyle = $request->input('sideMenuStyle');
        $saveData->AppTheme = $request->input('AppTheme');

        $saveData->facebook = $request->input('facebook');
        $saveData->youtube = $request->input('youtube');
        $saveData->twitter = $request->input('twitter');
        $saveData->instagram = $request->input('instagram');
        $saveData->linkedin = $request->input('linkedin');

        $saveData->whatsAppNumber = $request->input('whatsAppNumber');
        $saveData->whatsAppKey = $request->input('whatsAppKey');
        $saveData->telegram_key = $request->input('telegram_key');
        $saveData->telegram_group = $request->input('telegram_group');
        $saveData->telegram_phone = $request->input('telegram_phone');

        $saveData->save();

        foreach (config('app.web_lang') as $key => $lang) {
            $saveTranslation = AppSettingTranslation::where('setting_id', $saveData->id)->where('locale', $key)->firstOrNew();
            $saveTranslation->setting_id = $saveData->id;
            $saveTranslation->locale = $key;
            $saveTranslation->whatsAppMessage = $request->input($key . '.whatsAppMessage');
            $saveTranslation->AppName = $request->input($key . '.AppName');
            $saveTranslation->save();
        }
        self::ClearCash();
        return redirect()->back();
    }

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| #     AppPhotos
    public function AppPhotos() {
        $pageData = $this->pageData;
        $pageData['ViewType'] = "Edit";
        $setting = AppSetting::findOrFail(1);
        return view('AppPlugin.ConfigApp.app_photo')->with(compact('pageData', 'setting'));
    }

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| #
    public function photoUpdate(AppPhotoRequest $request, $id = '0') {

        $saveData = AppSetting::findOrFail(1);
        $fildeName = $request->input('cat_id');

        $saveImgData = new PuzzleUploadProcess();
        $saveImgData->setCountOfUpload('1');
        $saveImgData->setUploadDirIs('app-photo');
        $saveImgData->setnewFileName($request->cat_id);
        $saveImgData->UploadOne($request);

        if(count($saveImgData->sendSaveData) != 0) {
            if(File::exists($saveData->$fildeName)) {
                File::delete($saveData->$fildeName);
            }
            $saveData->$fildeName = $saveImgData->sendSaveData['photo']['file_name'];
            $saveData->save();
            self::ClearCash();
        }


        return redirect()->back();
    }

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| #   AppProfile
    public function AppProfile() {
        $pageData = $this->pageData;
        $pageData['ViewType'] = "Edit";

        if(Route::currentRouteName() == 'App.AppProfile.form') {
            $pageData['card'] = __('admin/configApp.app_menu_profile');
            $menu = AppMenu::where('type', 'user')->firstOrFail();
        } elseif(Route::currentRouteName() == 'App.AppCart.form') {
            $pageData['card'] = __('admin/configApp.app_menu_cart');
            $menu = AppMenu::where('type', 'cart')->firstOrFail();
        }

        return view('AppPlugin.ConfigApp.form')->with(compact('pageData', 'menu'));
    }

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
#|||||||||||||||||||||||||||||||||||||| #
    public function AppProfileUpdate(AppMenuRequest $request) {
        $id = $request->input('id');
        $menu = AppMenu::findOrFail($id);
        $menu->is_active = intval((bool)$request->input('is_active'));
        $menu->save();

        foreach (config('app.web_lang') as $key => $lang) {
            $saveTranslation = AppMenuTranslation::where('menu_id', $menu->id)->where('locale', $key)->firstOrNew();
            $saveTranslation->menu_id = $menu->id;
            $saveTranslation->locale = $key;
            $saveTranslation->url = $request->input($key . '.url');
            $saveTranslation->label = $request->input($key . '.label');
            $saveTranslation->icon = $request->input($key . '.icon');
            $saveTranslation->save();
        }
        return redirect()->back();
    }

}
