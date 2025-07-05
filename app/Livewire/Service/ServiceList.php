<?php

namespace App\Livewire\Service;

use Livewire\Component;
use App\Models\Service;
use App\Models\ServiceDetail;
use App\Models\Translation;
use Livewire\Attributes\Title;

class ServiceList extends Component
{
    public $services,$search_query,$lang;
    /* render the page */
    #[Title('Services')]
    public function render()
    {
        return view('livewire.service.service-list');
    }
    /* process before render */
    public function mount()
    {
        if(!\Illuminate\Support\Facades\Gate::allows('service_list')){
            abort(404);
        }
        $this->services = Service::latest()->get();
        if(session()->has('selected_language'))
        {   /* if session has selected language */
            $this->lang = Translation::where('id',session()->get('selected_language'))->first();
        }
        else{
            /* if session has no selected language */
            $this->lang = Translation::where('default',1)->first();
        }
    }
    /* delete the service */
    public function delete($id)
    {
    try {
        // Vérifie si le service existe
        $service = Service::findOrFail($id);

        // Supprime d'abord les détails liés
        ServiceDetail::where('service_id', $id)->delete();

        // Supprime ensuite le service
        $service->delete();

        // Rafraîchir la liste des services
        $this->services = Service::latest()->get();

        // Message de succès
        $this->dispatch(
            'alert', ['type' => 'success', 'message' => 'Service supprimé avec succès.']
        );
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Cas où le service n'existe pas
        $this->dispatch(
            'alert', ['type' => 'error', 'message' => 'Service introuvable !']
        );
    } catch (\Exception $e) {
        // Log l’erreur pour débogage
        logger()->error("Erreur suppression service ID $id : " . $e->getMessage());

        // Message d'erreur générique
        $this->dispatch(
            'alert', ['type' => 'error', 'message' => 'Impossible de supprimer le service.']
        );
    }
}
    /* process while update the content */
    public function updated($name,$value)
    {   /* if the updated element is search_query */
        if($name == 'search_query' && $value != '')
        {
            $this->services = Service::where('service_name', 'like' , '%'.$value.'%')->get();
        }
        elseif($name == 'search_query' && $value == ''){
            $this->services = Service::latest()->get();

        }
    }
}
