<?php

namespace App\Livewire\Installer;

use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Title;
use Livewire\Component;

class UpdaterApp extends Component
{
    public $running = false;
    public $hasLicense = false;
    public $license_code = "";
    public $client_name = "";
    //Check if update file is found, if not redirect
    public function mount()
    {
        $installFile = File::exists(base_path('update'));
        if (!$installFile) {
            Log::info('Install file exists: ' . ($installFile ? 'true' : 'false')); // Ajout du log

            return redirect('');
            
        }

        $expenseHelper = new InstallController();
        $validation = $expenseHelper->verify_license();
        if(isset($validation['status']) && $validation['status'] == true)
        {
           $this->hasLicense = true;
        }
    }

    #[Layout('components.layouts.install-layout'),Title('Laundry Updater')]
    public function render()
    {
        return view('livewire.installer.updater-app');
    }

    public function doChecks(){
        $license = new InstallController();
        $validation = $license->verify_license();
        if(!isset($validation['status']) || $validation['status'] != true){
            $verified = $license->activate_license($this->license_code,$this->client_name);
            if($verified['status'] == false){
                $this->addError('license_code',$verified['message']);
                return false;
            }
        }
        return true;
    }
    
    public function updateApp(){
        
        $this->running = true;
        Artisan::call('migrate');
        Artisan::call('optimize:clear');
        Artisan::call('config:cache');
        Artisan::call('db:seed');
        File::delete(base_path('update'));
        Log::info('hhh: ' . ($installFile ? 'true' : 'false')); // Ajout du log

        $this->running = false;
        return url('login');
    }
}
