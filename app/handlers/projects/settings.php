<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 10-9-2016
 * Time: 19:41
 */

$projectId = ( !empty( $_POST['projectId'] ) ? $_POST['projectId'] : "" );

header("Location: " . APPLICATION_HOME . "?project_settings=" . $projectId .  "");
exit();