<?php
/*
 * Name: Settings
 * Description: User settings
 */

class SwiftUserSetting extends Eloquent {
    protected $table = "user_settings";

    const TYPE_THEME = 1;

    public static $theme = [
        1 => 'Classic',
        2 => 'Dark',
        3 => 'Light',
        4 => 'Modern'
    ];

    public function user()
    {
        return $this->belongsTo('User','user_id');
    }
}

