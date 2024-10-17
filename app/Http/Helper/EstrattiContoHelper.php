<?php

namespace App\Http\Helper;
use App\carburanti\carb_aziende;
use App\aziende\azn_puntivendita;

class EstrattiContoHelper {
    protected $params = "";

    public function __construct($params) {
        $this->params = $params;
    }

    /**
     *  Ritorna l'anno di una data
     * @return int|string
     */
    public static function setAnno($params) {
        return date('Y', strtotime($params->data));
    }

    /**
     * Ritorna il mese di una data
     * @return int|string
     */
    public static function setMese($params) {
        return date('m', strtotime($params->data));
    }

    public static function getPuntoVendita(int $id_azn_puntovendita): azn_puntivendita {
        return azn_puntivendita::where('id_azn_puntovendita', $id_azn_puntovendita)->first();
    }

    public static function getAzienda($id) {
        return carb_aziende::where('id', $id)->first();
    }

    public function setFilename() {
        return date("F", mktime(0, 0, 0, $this->setMese(), 10)).'-'.$this->setAnno().'-'.$this->getAzienda()['ragsoc'].'-'.time();
    }

    public function getTransazioniAzienda() {

    }
}