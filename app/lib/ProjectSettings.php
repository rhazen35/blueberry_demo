<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 11-Sep-16
 * Time: 21:07
 */

namespace app\lib;

use app\model\Service;

if( !class_exists( "ProjectSettings" ) ):

    class ProjectSettings
    {
        protected $type;
        /**
         * ProjectSettings constructor.
         * @param $type
         */
        public function __construct($type )
        {
            $this->type = $type;
        }
        /**
         * @param $params
         */
        public function request($params )
        {
            switch( $this->type ):
                case"updateSettings":
                    $this->updateSettings( $params );
                    break;
            endswitch;
        }
        /**
         * @param $params
         */
        private function updateSettings($params )
        {
            $sql      = "CALL proc_updateSettings(?,?,?,?)";
            $data     = array(
                            "project_id" => $params['project_id'],
                            "name"       => $params['name'],
                            "descr"      => $params['descr'],
                            "type"       => $params['type']
                            );
            $format   = array("isss");
            $type     = "update";
            $database = "blueberry";
            ( new Service( $type, $database ) )->dbAction( $sql, $data, $format );
        }
    }

endif;