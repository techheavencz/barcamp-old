<?php

namespace App\Presenters;

use Nette;


final class HomepagePresenter extends BasePresenter
{
    public function renderContact() {
        $orgs = $this->db->table("people")->where("category", "org")->order("num_order");


        $this->template->organizators = $orgs;

        $asist = $this->db->table("people")->where("category", "asist")->order("num_order");

        $this->template->asistants = $asist;
    }
    public function renderDefault() {

    }
    public function renderInfo(){

    }
    public function renderPartners(){

    }
    public function renderPrivacyPolicy(){

    }
    public function renderTerms(){

    }
    public function renderVocabulary(){

    }
    public function renderWritten(){

    }
}
