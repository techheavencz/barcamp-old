<?php

namespace App\Presenters;

use Nette;


final class HomepagePresenter extends BasePresenter
{
    public function renderContact() {
        $v = [
            "full_name" => "Vojta Pšenák",
            "tel" => "721930266",
            "mail" => "vojta@techheaven.org",
            "facebook" => "psenak.vojtech",
            "twitter" => "VPsenak",
        ];
        $vojta = (object) $v;



        $organizators = [
            $vojta,
        ];



        $this->template->organizators = (object) $organizators;

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
