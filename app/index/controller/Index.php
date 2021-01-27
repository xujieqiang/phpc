<?php
namespace app\index\controller;
 
//use app\index\controller;
//require_once __DIR__ . '/../../../vendor/autoload.php';
//use app\validate\Shenfenzheng;

use app\BaseController;
use app\index\model\Dengjibiao;
use app\index\model\Machine;
use app\index\model\Record;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\middleware\SessionInit;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\FORMAT_TEXT;
//use think\exception\ValidateException;
//use think\Request;

class Index extends BaseController
{
	public function trspace($str=' '){
		$a=[];
		$a=explode(" ",$str);
		$b=[];
		$i=0;
		foreach($a as $key=>$val){
			if($val!=' '){
				$b[$i]=$val;
				$i++;
			}
		}
		return implode($b);
	}
    public function index()
    {
       	$nameval="";
		if(isset($_COOKIE["namecode"]))
		{			
			$nameval=$_COOKIE["namecode"];
			return redirect("index/index/ipv");
		}	
		View::assign("nameval",$nameval) ; 		
		return View::fetch();
    }
	public function  ipv()
	{
		return View::fetch();
	}
	
	//自动跳转登录的页面，通过ipv.html自动提交 ipc参数获取客户端ip地址，然后跳转到该页面
	public function successrs1()
	{		 
		$nameval=$_COOKIE["namecode"];
		$riqi1=date("Y-m-d");
		$ptime=date("H:i:s");
		$record=Db::name('dengjibiao')->where('name',$nameval)->where('riqi',$riqi1)->find();
		$msg='';
		$msg1='';		
		$info='';
		$i=0;
		$ip=$_POST['ipv'];		 
		//屏蔽重复打卡的行为
		if($record){
			$msg1=$msg1."已经打卡了，不能重复";
			$i++;
		}
		/* 
		if($ip=="115.238.224.30"){
			$msg=$msg."不能在室内打卡!";
			$i++;
		}*/
		//没有屏蔽的动作就进入添加数据库的动作
		if($i==0){
			$a= $this->getOS();
			$b=$this->mobile_type();
			$identi=$a.$b;
			//添加到数据表
			$model=new Dengjibiao;
			 
			
			$model['name']=$nameval;
			$model['riqi']=$riqi1;
			$model['ptime']=$ptime;										
			$model['identify']=$identi;
			 //记录ip详情
			$r_m=new Record;
			$r_m['name']=$nameval;
			$r_m['ostype']=$identi;
			$r_m['ip']=$ip;
			$r_m['shijian']	= date("Y-m-d H:i:s");
		    
			$fp=fopen("lock.txt","w+");
			if(flock($fp,LOCK_EX))
			{
		
				$sql=$model->save();
				flock($fp,LOCK_UN);
			}
			fclose($fp);
				
				
			
			 
			//$r_m->save();
			$msg="你于".$riqi1.",".$ptime."打卡";
			$info="打卡成功";
		}else{
			$nameval="失败原因：";
			$info="打卡失败";
		}
		
		View::assign('name',$nameval);
		View::assign('info',$info);
		View::assign('msg',$msg);
		View::assign('msg1',$msg1);
		return View::fetch();
	}
	
	function uuid($prefix = '') {
	    $chars = uniqid(mt_rand());
	    $uuid = substr($chars, 0, 8) . '-';
	    $uuid .= substr($chars, 8, 4) . '-';
	    $uuid .= substr($chars, 12, 4) . '-';
	    $uuid .= substr($chars, 16, 4) . '-';
	    $uuid .= substr($chars, 20, 12);
	    return $prefix . $uuid;
	}
	
	public function chuli()
	{
		$name1=$_POST['uname'];
		$ip=$_POST['ipv'];
		$name=$this->trspace($name1);
		 $xm_len = preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$name);
		$msg="";
		$i=0;
		if(!$xm_len){
			$msg .="请填写真实姓名；<br/>";
			$i++;
		}
		 
		if($i==0){
			$riqi=date("Y-m-d");
			$time=date("H:i:s");
			$a= $this->getOS();
			$b=$this->mobile_type();
			$identi=$a.$b;
			$rs=Db::name('dengjibiao')->where('name',$name)->where('riqi',$riqi)->find();
			if($rs)
			{
				$msg.="已经打卡<br/>";
				
			}else{				
				if(isset($_COOKIE["namecode"]))				
				{					
					$name_value=$_COOKIE["namecode"]; 
					//\print_r($_COOKIE) ;
					//dump($_COOKIE);
					//setcookie ("namecode", "", time() - 3600,"/");
					if($name_value!=$name)
					{
						//print_r($name_value);
						$msg .="不能为他人打卡<br />";
					}else{
						/*
						if($ip=="115.238.224.30"){
							$msg.="也许你应该去室外打卡<br />";
						}else{
						*/	
							$rs_m=Db::name('machine')->where('name',$name)->select();
							if(count($rs_m)>=1){
								foreach($rs_m as $key=>$rs1){
									if($rs1['ostype']!=$identi)
									{
										$msg.="不能帮人打卡，如更换设备请联系！<br />";
									}else{
										$r_m=new Record;
										$r_m['name']=$name;
										$r_m['ostype']=$identi;
										$r_m['ip']=$ip;
										$r_m['shijian']	= date("Y-m-d H:i:s");
											 
										$model=new Dengjibiao;										 
										$model['name']=$name;
										$model['riqi']=$riqi;
										$model['ptime']=$time;										
										$model['identify']=$identi;										 
										    
										$fp=fopen("lock.txt","w+");
										if(flock($fp,LOCK_EX))
										{
												
											$sql=$model->save();
											flock($fp,LOCK_UN);
										}
										fclose($fp);
																			
										if($sql)
										{
											//$r_m->save();
											$rsd =Db::name('dengjibiao')->where('name',$name)->where('riqi',$riqi)->find();											
											echo exit('<script>top.location.href="/paocao/public/index/index/successrs?id='.$rsd['id'].'" </script>');
										}
									}
								}
							}else{
								$r_m=new Record;
								$r_m['name']=$name;
								$r_m['ostype']=$identi;
								$r_m['ip']=$ip;
								$r_m['shijian']	= date("Y-m-d H:i:s");
								
								$machine_m=new Machine;
								$machine_m['name']=$name;
								$machine_m['ostype']=$identi;								
								
								$model=new Dengjibiao;										 
								$model['name']=$name;
								$model['riqi']=$riqi;
								$model['ptime']=$time;										
								$model['identify']=$identi;
								 $fp=fopen("lock.txt","w+");
								 if(flock($fp,LOCK_EX))
								 {
								 		
								 	$sql=$model->save();
								 	flock($fp,LOCK_UN);
								 }
								 fclose($fp);
								
								if($sql)
								{
									//$r_m->save();
									$machine_m->save();
									$rsd =Db::name('dengjibiao')->where('name',$name)->where('riqi',$riqi)->find();									
									echo exit('<script>top.location.href="/paocao/public/index/index/successrs?id='.$rsd['id'].'" </script>');
								}
							}							
						//}
					}
				}else{					
						/*
						if($ip=="115.238.224.30"){
							$msg.="也许你应该去室外打卡<br />";
						}else{
							*/
							$rs_m=Db::name('machine')->where('name',$name)->select();
							if(count($rs_m)>=1){
								foreach($rs_m as $key=>$rs1){
									/*
									if($rs1['ostype']!=$identi)
									{
										$msg.="不能帮人打卡，如更换设备请联系！<br />";
									}else{ */
										$r_m=new Record;
										$r_m['name']=$name;
										$r_m['ostype']=$identi;
										$r_m['ip']=$ip;
										$r_m['shijian']	= date("Y-m-d H:i:s");
											 
										$model=new Dengjibiao;										 
										$model['name']=$name;
										$model['riqi']=$riqi;
										$model['ptime']=$time;										
										$model['identify']=$identi;										 
										$fp=fopen("lock.txt","w+");
										if(flock($fp,LOCK_EX))
										{
												
											$sql=$model->save();
											flock($fp,LOCK_UN);
											\setcookie("namecode",$name,time()+3600*24*365*5,"/");
										}
										fclose($fp);
										
										
										if($sql)
										{
											//$r_m->save();
											$rsd =Db::name('dengjibiao')->where('name',$name)->where('riqi',$riqi)->find();											
											echo exit('<script>top.location.href="/paocao/public/index/index/successrs?id='.$rsd['id'].'" </script>');
										}
									//}
								}
							}else{
								$r_m=new Record;
								$r_m['name']=$name;
								$r_m['ostype']=$identi;
								$r_m['ip']=$ip;
								$r_m['shijian']	= date("Y-m-d H:i:s");
								
								$machine_m=new Machine;
								$machine_m['name']=$name;
								$machine_m['ostype']=$identi;								
								
								$model=new Dengjibiao;										 
								$model['name']=$name;
								$model['riqi']=$riqi;
								$model['ptime']=$time;										
								$model['identify']=$identi;	   
								 $fp=fopen("lock.txt","w+");
								 if(flock($fp,LOCK_EX))
								 {
								 		
								 	$sql=$model->save();
								 	flock($fp,LOCK_UN);
								 	\setcookie("namecode",$name,time()+3600*24*365*5,"/");
								 }
								 fclose($fp);
								 
								if($sql)
								{
									//$r_m->save();
									$machine_m->save();
									$rsd =Db::name('dengjibiao')->where('name',$name)->where('riqi',$riqi)->find();									
									echo exit('<script>top.location.href="/paocao/public/index/index/successrs?id='.$rsd['id'].'" </script>');
								}
							}							
						//}
				}
			}
		}
		$msg="<font color='red'>".$msg."</font>";
		return $msg;
	}
	
	public function save()
	{
		/*
		 $data = [
	    ['title1' => '11a1', 'title2' => 'b222'],
	    ['title1' => '11d1', 'title2' => '2c22'],
	    ['title1' => "330522918109301519", 'title2' => '22312312312312312312']
			//  ['title1' => "330522918109301519"."\t", 'title2' => 'f222'] 此方法可以防止身份证变成科学计数法
	];
	*/
	$title = ['id','日期','姓名', '时间','手机'];
	 
	// Create new Spreadsheet object
	$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();	
	$sheet = $spreadsheet->getActiveSheet();
		     
	  
	//表头
	//设置单元格内容
	$titCol = 'A';
	foreach ($title as $key => $value) {
	    // 单元格内容写入
	    $sheet->setCellValue($titCol . '1', $value)->getColumnDimension($titCol)->setAutoSize(true);
	    $titCol++;
	}
	$row = 2; // 从第二行开始   setWidth(30)
	$riqi=date("Y-m-d");
	//$riqi="2020-12-02";
	$data=Db::name('dengjibiao')->where('riqi',$riqi)->order('name','asc')->select();
	foreach ($data as $key1=>$item) {
	    $dataCol = 'A';				
	    foreach ($item as $key=>$value) {
	        // 单元格内容写入
	      //  $sheet->setCellValue($dataCol . $row, $value)->getStyle($dataCol.$row)
	//->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
	//跳过 checkp
	if($key=='checkp'){
		$value='';
	}
	
	  $sheet->setCellValueExplicit($dataCol.$row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING)->getStyle($dataCol.$row)
	->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
	       $dataCol++;
	    }
	    $row++;
	}
	 
	// Save
	$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
	$writer->save(__DIR__.'/../../../file/数据.xlsx');
	}
	
	
	public function insert_data($name,$riqi,$time,$identi)
	{
		
		$model=new Dengjibiao;
		 
		$model['name']=$name;
		$model['riqi']=$riqi;
		$model['ptime']=$time;
		
		$model['identify']=$identi;
		
	   
	  
	   
		$sql=$model->save();
		if($sql)
		{
			$rsd =Db::name('dengjibiao')->where('name',$name)->where('riqi',$riqi)->find();
			
			
			echo exit('<script>top.location.href="/paocao/public/index/index/successrs?id='.$rsd['id'].'" </script>');
		}
	}
	
	public function successrs(){
		$id = $_GET['id'];
		$record=Dengjibiao::find($id);
		View::assign('res',$record);
		
		return View::fetch();
	}
	
	public function chaxun(){
		
		$res=Db::name('dengjibiao')->order('riqi','desc')->select();
		$a[0]="";
		$i=0;
		foreach($res as $key=>$value)
		{
			//foreach($value as $key1=>$value1){
			//	dump($value1) ;
				$a[$i]=substr($value['riqi'],0,7);
				$i++;
			
		}
		
		$a1=array_unique($a);
		$b[0]="";
		$c[0]="";
		$j=0;
		$d=[];
		
		foreach($a1 as $avalue){
			$rs=Db::name('dengjibiao')->order('riqi','asc')->where('riqi','like',$avalue.'%')->select();
			foreach($rs as $key=>$value){
				$c[$j]=$value['name'];
				$j++;
			}
			$count_c=[];
			foreach($c as $key => $v){
				$count_c[$v]=0;
			}
			foreach($c as $key => $v){
				$count_c[$v]++;
			}
			$j=0;
			$c=[];
			$m[0]="";
			$k=0;
			foreach($count_c as $key=>$v){
				$m[$k]=[$key,$v];
				$k++;
			}
			$d[$avalue]=$m;
			
		}
		
		View::assign('d',$d);
		return View::fetch();
		
	}
	
	
	public function getOS()
	{
	        $ua = $_SERVER['HTTP_USER_AGENT'];
	        if (strpos($ua, 'Android') !== false) {
	            preg_match("/(?<=Android )[\d\.]{1,}/", $ua, $version);
	            return 'Platform:Android OS_Version:'.$version[0];
	        } elseif (strpos($ua, 'iPhone') !== false) {
	            preg_match("/(?<=CPU iPhone OS )[\d\_]{1,}/", $ua, $version);
	            return 'Platform:iPhone OS_Version:'.str_replace('_', '.', $version[0]);
	        } elseif (strpos($ua, 'iPad') !== false) {
	            preg_match("/(?<=CPU OS )[\d\_]{1,}/", $ua, $version);
	            return 'Platform:iPad OS_Version:'.str_replace('_', '.', $version[0]);
	        } 
	        
	}
	 
	//型号
	 
	public function mobile_type()
	{
	        $user_agent = $_SERVER['HTTP_USER_AGENT'];
	        if (stripos($user_agent, "iPhone")!==false) {
	            $brand = 'iPhone';
	        } else if (stripos($user_agent, "SAMSUNG")!==false || stripos($user_agent, "Galaxy")!==false || strpos($user_agent, "GT-")!==false || strpos($user_agent, "SCH-")!==false || strpos($user_agent, "SM-")!==false) {
	            $brand = '三星';
	        } else if (stripos($user_agent, "Huawei")!==false || stripos($user_agent, "Honor")!==false || stripos($user_agent, "H60-")!==false || stripos($user_agent, "H30-")!==false) {
	            $brand = '华为';
	        } else if (stripos($user_agent, "Lenovo")!==false) {
	            $brand = '联想';
	        } else if (strpos($user_agent, "MI-ONE")!==false || strpos($user_agent, "MI 1S")!==false || strpos($user_agent, "MI 2")!==false || strpos($user_agent, "MI 3")!==false || strpos($user_agent, "MI 4")!==false || strpos($user_agent, "MI-4")!==false) {
	            $brand = '小米';
	        } else if (strpos($user_agent, "HM NOTE")!==false || strpos($user_agent, "HM201")!==false) {
	            $brand = '红米';
	        } else if (stripos($user_agent, "Coolpad")!==false || strpos($user_agent, "8190Q")!==false || strpos($user_agent, "5910")!==false) {
	            $brand = '酷派';
	        } else if (stripos($user_agent, "ZTE")!==false || stripos($user_agent, "X9180")!==false || stripos($user_agent, "N9180")!==false || stripos($user_agent, "U9180")!==false) {
	            $brand = '中兴';
	        } else if (stripos($user_agent, "OPPO")!==false || strpos($user_agent, "X9007")!==false || strpos($user_agent, "X907")!==false || strpos($user_agent, "X909")!==false || strpos($user_agent, "R831S")!==false || strpos($user_agent, "R827T")!==false || strpos($user_agent, "R821T")!==false || strpos($user_agent, "R811")!==false || strpos($user_agent, "R2017")!==false) {
	            $brand = 'OPPO';
	        } else if (strpos($user_agent, "HTC")!==false || stripos($user_agent, "Desire")!==false) {
	            $brand = 'HTC';
	        } else if (stripos($user_agent, "vivo")!==false) {
	            $brand = 'vivo';
	        } else if (stripos($user_agent, "K-Touch")!==false) {
	            $brand = '天语';
	        } else if (stripos($user_agent, "Nubia")!==false || stripos($user_agent, "NX50")!==false || stripos($user_agent, "NX40")!==false) {
	            $brand = '努比亚';
	        } else if (strpos($user_agent, "M045")!==false || strpos($user_agent, "M032")!==false || strpos($user_agent, "M355")!==false) {
	            $brand = '魅族';
	        } else if (stripos($user_agent, "DOOV")!==false) {
	            $brand = '朵唯';
	        } else if (stripos($user_agent, "GFIVE")!==false) {
	            $brand = '基伍';
	        } else if (stripos($user_agent, "Gionee")!==false || strpos($user_agent, "GN")!==false) {
	            $brand = '金立';
	        } else if (stripos($user_agent, "HS-U")!==false || stripos($user_agent, "HS-E")!==false) {
	            $brand = '海信';
	        } else if (stripos($user_agent, "Nokia")!==false) {
	            $brand = '诺基亚';
	        } else {
	            $brand = '其他手机';
	        }
	        return $brand;
	}

}