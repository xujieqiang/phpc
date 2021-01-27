<?php
namespace app\index\controller;
//use app\index\controller;
//require_once __DIR__ . '/../../../vendor/autoload.php';
//use app\validate\Shenfenzheng;

use app\BaseController;
use app\index\model\Laoshi as LaoshiModel;
use app\index\model\Xuanpiao as XuanpiaoModel;
use app\index\model\Dierlunls as DierlunlsModel;
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
    public function index()
    {
        //return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) 2020新春快乐</h1><p> ThinkPHP V' . \think\facade\App::version() . '<br/><span style="font-size:30px;">14载初心不改 - 你值得信赖的PHP框架</span></p><span style="font-size:25px;">[ V6.0 版本由 <a href="https://www.yisu.com/" target="yisu">亿速云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=64890268" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ee9b1aa918103c4fc"></think>';
		//Route::post('index/index','index/index')->token();
		
		//echo $this->getIp();
		//$iipp = $_SERVER["REMOTE_ADDR"];
		//echo $iipp;
		return View::fetch();
    }
	
	function getIp()
	{
	    if ($_SERVER["HTTP_CLIENT_IP"] && strcasecmp($_SERVER["HTTP_CLIENT_IP"], "unknown")) {
	        $ip = $_SERVER["HTTP_CLIENT_IP"];
	    } else {
	        if ($_SERVER["HTTP_X_FORWARDED_FOR"] && strcasecmp($_SERVER["HTTP_X_FORWARDED_FOR"], "unknown")) {
	            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	        } else {
	            if ($_SERVER["REMOTE_ADDR"] && strcasecmp($_SERVER["REMOTE_ADDR"], "unknown")) {
	                $ip = $_SERVER["REMOTE_ADDR"];
	            } else {
	                if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'],
	                        "unknown")
	                ) {
	                    $ip = $_SERVER['REMOTE_ADDR'];
	                } else {
	                    $ip = "unknown";
	                }
	            }
	        }
	    }
	    return ($ip);
	}
	
	public function tongji()
	{
		$a1[0]='';
		$a2[0]='';
		$a3[0]='';
		$i=0;
		//$xuanpiao1=DierlunlsModel::select()->order('lsxm asc');
		$xuanpiao1=Db::name('disanlunxp')->order('zkeails','asc')->select();
		foreach($xuanpiao1 as $key=>$xp)
		{
			$a1[$i]=$xp['zkeails'];
			$i++;
		}
		$lsmd= array_unique($a1);
		$b1[0]='';
		$b2[0]='';
		$b3[0]='';
		$m=0;
		$n=0;
		$k=0;
		foreach($lsmd as $key=>$val)
		{
			$data=Db::name('disanlun')->order('nianji','asc')->where('xingming',$val)->find();
			$nj=$data['nianji'];
			if($nj==7)
			{
				$b1[$m]=$val;
				$m++;
			}
			if($nj==8)
			{
				$b2[$n]=$val;
				$n++;
			}
			if($nj==9)
			{
				$b3[$k]=$val;
				$k++;
			}
		}
		
		
		$c1[0]='';
		$c2[0]='';
		$c3[0]='';
		$j=0;
		foreach($b1 as $key=>$v1)
		{
			$num=count(Db::name('disanlunxp')->order('zkeails','asc')->where('zkeails',$v1)->select());
			$c1[$j]=[$v1,$num];
			$j++;
		}
		foreach($b2 as $key=>$v1)
		{
			$num=count(Db::name('disanlunxp')->order('zkeails','asc')->where('zkeails',$v1)->select());
			$c2[$j]=[$v1,$num];
			$j++;
		}
		foreach($b3 as $key=>$v1)
		{
			$num=count(Db::name('disanlunxp')->order('zkeails','asc')->where('zkeails',$v1)->select());
			$c3[$j]=[$v1,$num];
			$j++;
		}
		
		 
		View::assign('list1',$c1);
		View::assign('list2',$c2);
		View::assign('list3',$c3);
		return View::fetch();
	}
	
	public function test()
	{
		return View::fetch();
	}
	   

	public function cl()
	{
		$q = isset($_GET["q"]) ? intval($_GET["q"]) : '';
		$val='';
		$a[0]="";
 
		if(empty($q)) {
			echo '请选择一个年级';
			
			exit;
		}else{
			$query=DierlunlsModel::where('nianji',$q)->order('nianji','asc')->select();
			$num=count($query); 
			$val=$val.'<table  class="layui-table"  lay-skin="line">';
			$i=0;
			foreach($query as $key=>$item)
			{
				$a[$i]=$item['xingming'];
				if($i % 2==0)
				{
					if($i+1==$num)
					{
						$val=$val.'<tr><td colspan=2><p><input type="checkbox" name="b[]" value="'.$item['xingming'].'" />'.$item['xingming'].'</td>';
						break;
					}
					$val=$val.'<tr><td><p><input type="checkbox" name="b[]" value="'.$item['xingming'].'" />'.$item['xingming'].'</td>';
					
				}else{
					
					$val=$val.'<td><input type="checkbox" name="b[]" value="'.$item['xingming'].'" />'.$item['xingming'].'</p></td></tr>';
				}
				$i++;
			}
			$val=$val.'</table>';
			
		}
		
		
		//return ($a); 
		return ($val);
	}
	
	public function shujucl()
	{
		
		

		$nj=Request::param('njj');
		$sf=Request::param('sf');
		$xm=Request::param('xm');
		$ls=Request::param('b');
		 $len = preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$xm);
		 
		 
			 
			$msg="";
			$i=0;
		if($nj=="")
		{
			$msg=$msg."年级没有选择！<br />";
			$i++;
			
		}
		if($sf=="")
		{
			$msg=$msg."没有选择'身份'！<br />";
			$i++;
			
		}
		if(!$len)
		{
			$msg=$msg."请填写真实姓名!<br />";
			$i++;
			
		}
		if($ls==null)
		{
			$msg=$msg."你没有选择你最喜爱的老师<br />";
			$i++;
		}
		$num=0;
		foreach($ls as $key=>$vo)
		{
			$num++;
			
		}
		if($num!=2)
		{
			$msg=$msg.'老师必须且只能选择2个';
			$i++;
		}
		
		if($i==0)
		{
			
			/* $xuanpiao=new DierlunlsModel();
			 */
			$rs=Db::name('disanlunxp')->where('sf',$sf)->where('xingming',$xm)->select();
			 
			if(count($rs)>0)
			{
				//更新原有记录
				$tag=0;
				foreach($rs as $key=>$vo)
				{
					$gx=Db::name('disanlunxp')->save(['id'=>$vo['id'], 'zkeails'=>$ls[$tag]]);
					$tag++;
					if(!$gx)
					{
						$msg=$msg.'<br />更新失败！';
						return $msg;
					}
				}
				$and='  和  ';
				echo exit('<script>top.location.href="/tp4/public/index/index/successrs?ls='.$ls[0].'&ls1='.$ls[1].'" </script>');
				
			}else{
				//新增记录
				$data=[
				'nianji'=>$nj,
				'sf'=>$sf,
				'xingming'=>$xm,
				'zkeails'=>$ls[0]
				];
				$data1=[
				'nianji'=>$nj,
				'sf'=>$sf,
				'xingming'=>$xm,
				'zkeails'=>$ls[1]
				];
				 
				$xz=Db::name('disanlunxp')->save($data);
				$xz1=Db::name('disanlunxp')->save($data1);
				if($xz && $xz1)
				{
					echo exit('<script>top.location.href="/tp4/public/index/index/successrs?ls='.$ls[0].'&ls1='.$ls[1].'" </script>');
				}
			}
			
		}
		$msg="<font color='red'>".$msg."</font>";
		return $msg;
		
	}
	
	public function successrs()
	{
		$ls=$_GET['ls'];
		$ls1=$_GET['ls1'];
		View::assign('ls',$ls);
		View::assign('ls1',$ls1);
		return View::fetch();
	}
	
	public function wodels()
	{
		$bj=Request::param('p');
		$nj=Request::param('q');
		if($nj=="")
		{
			echo "请选择年级";
			exit;
		}
		$newbj=$nj.$bj;
		$laoshi=LaoshiModel::select()->where('banji',$newbj);
		$front='<select name="ls"  >
				<option value="">选择老师</option>';
		$end='</select>';
		$middle='';
		foreach($laoshi as $key=>$list)
		{
			$middle=$middle.'<option value="'.$list["lsxm"].'" >'.$list["lsxm"].'</option>';
		}
		$xuanzels=$front.$middle.$end;
		
		return  $xuanzels ;
	}
	
	public function panduansfz()
	{
		//$str = input('sfz');
		 $str=$_POST['sfz'];
		//header("Content-type:text/html;charset=utf-8");
		//$str=$sfz;
		$rs=$this->isCreditNo($str);
		if($rs==true)
			{
			$msg="身份证号码正确";
			$tag=1;
			echo exit('<script>top.location.href="/tp/public/index/index/read"</script>');
			//return redirect('/tp/public/index/index/read');
		}else{
			$msg="身份证号码错误！请改正！";
			//$msg="<img src=\"/tp/public/static/pic/2.jpg\" height=\"150\" width=\"150\" />";
			$tag=0;
		}
		 //$data=[$msg,$str];
		 $data="<font color='red' ><h4>".$msg."</h4></font>";
		 $x="<font color='red'>".$msg."</font>";
		// return $x;
		return $data;
		//return json($data);
		//return $msg;  
	}
	

	/**
 * 判断是否为合法的身份证号码
 * @param $mobile
 * @return int
 */
 public function sfz($str)
{
	 $result=$this->isCreditNO($str);
	 if($result==false)
	{
	   dump("身份证号码错误");
	 }		
	 else{
		 dump("身份证号码正确！！！");
	 }
 }
public function isCreditNo($vStr){
 $vCity = array(
  '11','12','13','14','15','21','22',
  '23','31','32','33','34','35','36',
  '37','41','42','43','44','45','46',
  '50','51','52','53','54','61','62',
  '63','64','65','71','81','82','91'
 );
 if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;
 if (!in_array(substr($vStr, 0, 2), $vCity)) return false;
 $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
 $vLength = strlen($vStr);
 if ($vLength == 18) {
  $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
 } else {
  $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
 }
 if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
 if ($vLength == 18) {
  $vSum = 0;
  for ($i = 17 ; $i >= 0 ; $i--) {
   $vSubStr = substr($vStr, 17 - $i, 1);
   $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
  }
  if($vSum % 11 != 1) return false;
 }
 return true;
}

    public function hello()
    {
        $id=$_POST['sfz'];
		return json($id);
    }
}
