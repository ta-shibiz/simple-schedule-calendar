<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function ssc_get_fields() {
    return [
        'base_ub' => 'BASE（全面）',
        'base_u'  => 'BASE Uフィールド',
        'base_b'  => 'BASE Bフィールド',
        'met_lr'  => 'MET（全面）',
        'met_l'   => 'MET Lフィールド',
        'met_r'   => 'MET Rフィールド',
        'base_met'=> 'BASE×MET',
    ];
}