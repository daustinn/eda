<?php

namespace App\Http\Controllers;

use App\Models\Acceso;
use App\Models\Colaboradore;
use App\Models\Eda;
use App\Models\EdaColab;
use App\Models\Objetivo;
use App\Models\Supervisore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GlobalController extends Controller
{
    //OBJETIVOS

    public function getObjetivosByCurrentColab()
    {
        $edaColab = $this->getCurrentEdaByCurrentColab();
        $objetivos = Objetivo::where('id_eda_colab', $edaColab->id)->orderBy('created_at', 'desc')->get();
        return $objetivos;
    }


    // GLOBAL
    public function checkSupervisor()
    {
        $colaborador = $this->getCurrentColab();
        $firstColaborador = $this->getSupervisorByColabId($colaborador->id);
        if ($firstColaborador) return true;
        else return false;
    }

    public function checkSupervisorByIdColab($colab_id)
    {
        $firstColaborador = $this->getSupervisorByColabId($colab_id);
        if ($firstColaborador) return true;
        else return false;
    }


    // SUPERVISORES

    public function getSuperByCurrentColab()
    {
        $colab = $this->getCurrentColab();
        $edaColab = EdaColab::where([
            'id_colaborador' => $colab->id,
            'wearing' => 1
        ])->get();
        return $edaColab->supervisor;
    }




    public function getCurrentColab()
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }
        return $user->colaborador;
    }

    public function getColabByUser($id)
    {
        $colab = Colaboradore::where([
            'id_usuario' => $id,
        ])->first();
        return $colab;
    }

    // EDA

    public function getCurrentEda()
    {
        return Eda::where('wearing', 1)->first();
    }

    public function getCurrentEdaByCurrentColab()
    {
        $colaborador = $this->getCurrentColab();
        return $this->getEdaByColabId($colaborador->id);
    }


    public function getEdaByColabId($id)
    {
        return  EdaColab::where([
            'id_colaborador' => $id,
            'wearing' => 1
        ])->first();
    }

    // --------------------------------- ACCESOS----------------------------------
    function getColab()
    {
        $user = auth()->user();
        if ($user) return $user->colaborador;
    }
    function getAccesoPuestos()
    {
        $colab = $this->getColab();
        return Acceso::where('id_colaborador', $colab->id)
            ->where('modulo', 'puestos')
            ->first();
    }
    function getAccesoColaboradores()
    {
        $colab = $this->getColab();
        return Acceso::where('id_colaborador', $colab->id)
            ->where('modulo', 'colaboradores')
            ->first();
    }
    function getAccesoAreas()
    {
        $colab = $this->getColab();
        return Acceso::where('id_colaborador', $colab->id)
            ->where('modulo', 'areas')
            ->first();
    }
    function getAccesoEdas()
    {
        $colab = $this->getColab();
        return Acceso::where('id_colaborador', $colab->id)
            ->where('modulo', 'edas')
            ->first();
    }
    function getAccesoDepartamentos()
    {
        $colab = $this->getColab();
        return Acceso::where('id_colaborador', $colab->id)
            ->where('modulo', 'departamentos')
            ->first();
    }
    function getAccesoAccesos()
    {
        $colab = $this->getColab();
        return Acceso::where('id_colaborador', $colab->id)
            ->where('modulo', 'accesos')
            ->first();
    }
    function getAccesoCargos()
    {
        $colab = $this->getColab();
        return Acceso::where('id_colaborador', $colab->id)
            ->where('modulo', 'cargos')
            ->first();
    }
    function getAccesoSedes()
    {
        $colab = $this->getColab();
        return Acceso::where('id_colaborador', $colab->id)
            ->where('modulo', 'sedes')
            ->first();
    }
    function getAccesoObjetivos()
    {
        $colab = $this->getColab();
        return Acceso::where('id_colaborador', $colab->id)
            ->where('modulo', 'objetivos')
            ->first();
    }
    function getAccesoReportes()
    {
        $colab = $this->getColab();
        return Acceso::where('id_colaborador', $colab->id)
            ->where('modulo', 'reportes')
            ->first();
    }
}
