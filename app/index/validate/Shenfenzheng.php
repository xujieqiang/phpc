<?php
namespace app\index\validate;
use think\Validate;

class Shenfenzheng extends Validate
{
	 protected $rule = [
        'sfz'  =>  'require|lenth:18',
        //'email' =>  'email',
    ];
}