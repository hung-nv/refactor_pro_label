<?php


namespace Swissup\ProLabels\interfaces;


interface AbstractLabelInterface
{
    public function get_stock_qty($product);

    public function is_new_product($product);

    public function get_upload_image_label($image_path, $instance);

}