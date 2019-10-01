<?php


namespace App\Helpers;


class Alert
{
    public static function alert($type, $message)
    {
        $alert = '<div class="alert alert-'. $type .' alert-dismissible fade show" role="alert">' . $message . ' <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
        return $alert;
    }
}
