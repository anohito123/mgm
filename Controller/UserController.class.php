<?php
namespace Admin\Controller;
use Admin\Logic\UserList;

header('Content-Type:text/html; charset=utf-8');
class UserController extends BaseController {

        public function protect_ctr(){

            $m = M('GameBuyControl');

            $data = $m->select();

            $this->assign('data',$data);

            $this->display();
        }

    public function change_pctr(){

        $m = M('GameBuyControl');

        $data['IsOpen'] = I('post.isopen');
        $data['IsOpenNewUser'] = I('post.isopennewuser');
        $data['IsOpenUserControl'] = I('post.isopenusercontrol');

        $map['Id'] = 1;

        $m->where($map)->save($data);

        $this->redirect('protect_ctr');

    }

	public function bug_user(){
		$m = M('BugUsers','','RDDB_RECOED');
		$data = $m->order('lScore asc')->select();
		//dump($data);
		$this->assign('data',$data);

		$this->display();	
	}



    public function add_player(){
            $this->display();
        }

        public function only_v(){
            $m = M('AccountsInfo');
            $uid = I('get.userid');

            $map['GameID'] = $uid;

            $data = $m->where($map)->select();


            $this->assign('data',$data);
            $this->display();
        }

        public function auto_ctrl_list(){
            $m = M('Web_Auto_List','','RDDB_USER');
            $result = $m->select();

            $m1 = M('Ctrl_Config','','RDDB_USER');
            $cnf = $m1->select();

            foreach ($result as $k=>$v){
                $result[$k]['endcheatdate'] = $result[$k]['endcheatdate']?$result[$k]['endcheatdate']:'--';
            }

            $this->assign('data',$result);
            $this->assign('cnf',$cnf);
            $this->display();

        }
        public function do_auto_ctrl(){
            $m = M('Web_Auto_List','','RDDB_USER');

            $res=$m->query("EXEC [dbo].[Web_Auto_Ctrl_List]");

            $this->success('点控已生效！');

        }

        public function auto_ctrl_update(){
            $m = M('Web_Auto_List','','RDDB_USER');

            $flag = I('get.flag')?I('get.flag'):0;
            $daynum = (int)I('get.daynum')?(int)I('get.daynum'):0;
            $x = (float)I('get.x')+0;
            $cheat = (int)I('get.cheat')?(int)I('get.cheat'):0;
            $winlimit = (int)I('get.winlimit')?(int)I('get.winlimit'):0;


            if((is_float($x) || is_int($x)) && is_int($daynum) ){
                $result = $m->query("declare @count int exec @count = [dbo].[Web_Update_Ctrl] "."'".$flag."'".",'".$daynum."','".$x."','".$cheat."','".$winlimit."' select @count");

                $this->success('修改成功！');
            }else{
                $this->error('参数有误！');
            }



        }



    public function go_add_player(){
            $m = M('AccountsInfo');
            $m4 = M('WebOpLog');

        $phone = I('post.phone');
            $pwd = md5(I('post.pwd'));
            $gameid = I('post.gameid');


            //$result = $m->query("declare @count int exec @count = [dbo].[AA_Pro_UserInfo_RegisterWithGameId] "."'".$phone."'".",'".$pwd."',".$gameid." select @count");
        $result = $m->query("declare @count int exec @count = [dbo].[DBRequest_UserRegisterWithGameId] "."'".$gameid."'".",'".$phone."','".$pwd."' select @count");

        //$result = $result[0]['errordescribe'];

		
            if($result[0]['']==0){
                $uid = $gameid - 10000;
                $web['UserId'] = $uid;
                if(!$m4->where('UserId='.$web['UserId'])->select()){
                    $m4->add($web);
                }
                echo"注册的游戏ID:".$gameid."<br>"."提示：-";
				
				if($result[0]['errordescribe']==null){
					echo '注册成功！';
				}else{
					echo $result[0]['errordescribe'];
				}
                //$this->success('注册成功！');
            }else{
                $this->error($result[0]['errordescribe']);
            }


        }



        public function player_list(){
            $sort = I('get.sort',1);
            $_SESSION['online_sort'] = 1;

            $userid = I('get.userid');
            $detail_id = I('get.detail_id');
            $d_id = I('get.d_id');

            $roomname = I('get.roomname');
            $usertype = I('get.usertype');
            $vip_count = I('get.vip_count');
            $sum_count = I('get.sum_count');
            $order = I('get.order');

            $online_list = I('get.online_list');
            $online_list1 = I('get.online_list1');

            $protect_list = I('get.protect_list');

            $pid = I('get.pid');
            $stime = I('get.stime');
            $etime = I('get.etime');
            $stime1 = I('get.stime1');
            $etime1 = I('get.etime1');

            $nick =  I('get.nick');
            $phone =  I('get.phone');
            $lastip =  I('get.lastip');
            $code =  I('get.code');
            $plat = I('get.plat');

            $buy_min = I('get.buy_min');
            $buy_max = I('get.buy_max');
            $win_min = I('get.win_min');
            $win_max = I('get.win_max');

            $page_count = I('get.page_count',30);

        if($userid || $detail_id || $d_id ){

            $d_id?$map['UserID'] =  $d_id - 10000:($userid?$map['UserID'] =  $userid - 10000:$map['UserID'] =  $detail_id);

        }elseif ($roomname||$usertype||$vip_count || $sum_count || $order ){


            if($roomname){
                if($roomname=='hall'){
                    $map['t2.ServerID'] = 0;

                }elseif ($roomname==1){
                    $map['t2.ServerID'] = array('like','6%%1');
                }elseif ($roomname==2){
                    $map['t2.ServerID'] = array('like','6%%2');
                }elseif ($roomname==3){
                    $map['t2.ServerID'] = array('like','6%%3');
                }elseif ($roomname==4){
                    $map['t2.ServerID'] = array('like','6%%4');
                }else{
                    $map['t2.ServerID'] = array('like',$roomname.'%');
                }
            }

            if($usertype|| $vip_count|| $sum_count){
                if($usertype=='zero'){
                    $map['t1.usertype'] = 0;
                }elseif ($usertype=='all'|| $sum_count){

                        if(!$roomname){
                            $map = null;
                        }

                }else{

                    $map['t1.usertype'] = 4;
                }
            }


        }
        else{
            $online_list?$map=null:$map['UserID'] =  array('gt','10000');

        }


        $user = new UserList();

            if($detail_id || $d_id){

                $data = $user->data1('player_list',$map,$sort,true);

                $this->assign('data',$data['data']);
                $this->assign('page',$data['show']);

                //dump($data['data']);
                $this->display('player_detail');


            }elseif($online_list||$online_list1){
                $remark = I('get.remark','');
                $_SESSION['p'] = I('get.p',1);
                if(!$vip_count&&!$sum_count){
                    $_SESSION['page']='';
                }else{
                    $vip_count?$_SESSION['page'] = 'vip_count':'';
                    $sum_count?$_SESSION['page'] = 'sum_count':'';
                }

                if(!$usertype&&!$vip_count&&!$sum_count){
                    $map['t1.usertype'] = 0;
                }

                if($online_list1){
                    //dump($map);
                    unset($map['UserID']);
                    $map['t1.UserID'] = array('gt',10000);
                    $data = $user->data2($map,$sort,$remark);
                    $this->assign('data',$data['data']);
                    $this->assign('page',$data['show']);
                    $this->assign('count',$data['count']);


                    $this->display('online_list1');
                }else{


                    //$data = $user->data1('online_list',$map,$sort,false,$remark);
                    $data = null;
                    $this->assign('data',$data['data']);
                    $this->assign('page',$data['show']);
                    $this->assign('count',$data['count']);
                    $this->display('online_list');
                }

        
                //$data = $user->data1('online_list',$map,$sort,false,$remark);


            }elseif($protect_list){

                $pid?$map['ProtectID'] = $pid:'';

                $data = $user->data1('protect_list',$map);

                $this->assign('data',$data['data']);
                $this->assign('page',$data['show']);
                $this->assign('count',$data['count']);

                //dump($data);
                $this->display('protect_list');
            }else{

                $nick?$map['NickName'] =array('like',$nick.'%') :'';
                $phone?$map['PhoneNum'] =$phone :'';
                $lastip?$map['LastLogonIP'] =$lastip :'';
                $code?$map['MachineSerial'] =$code :'';
                $plat?$map['AppPlatform'] =$plat:'';

                if($buy_max!=null && $buy_min!=null){
                    $map_buy['max'] = $buy_max;
                    $map_buy['min'] = $buy_min;
                }else{
                    $map_buy = null;
                }


                $win_max?$map_win['max'] = $win_max:'';
                $win_min?$map_win['min'] = $win_min:'';

                if($stime&&$etime){

                    $map['LastLoginTime'] = array('between',date('Y-m-d',$stime).','.date('Y-m-d',$etime));
                }
                if($stime1&&$etime1){

                    $map['RegisterDate'] = array('between',date('Y-m-d',$stime1).','.date('Y-m-d',$etime1));
                }

                if($usertype=='pt'){
                    $map['usertype'] = array('neq',4);
                    $map['UserID'] = array('gt',10000);

                    unset($map['t1.usertype']);
                }
                if($usertype=='4'){
                    $map['usertype'] = 4;
                    unset($map['t1.usertype']);
                }

                $data = $user->data1('player_list',$map,$sort,false,0,$map_buy,$map_win,$page_count);

                $this->assign('data',$data['data']);
                $this->assign('page',$data['show']);
                $this->assign('count',$data['count']);
                $this->display();
            }

    }

    public function get_buy_zero(){
            $stime = I('get.stime',0);
        $etime = I('get.etime',0);

        if($stime!=0 && $etime!=0){
            $mapdate['RegisterDate'] =  array('between',date('Y-m-d',$stime).','.date('Y-m-d',$etime));

            dump('注册日期：'.date('Y-m-d',$stime).'——'.date('Y-m-d',$etime));
        }else{
            $mapdate['UserID'] = array('gt',0);
        }



        $m = M('BuyZeroID','','RDDB_USER');
        $m1 = M('AccountsInfo');
        $count = $m->where($mapdate)->count();
        $page = new \Think\Page($count,30);
        $show = $page->show();
        $data = $m->where($mapdate)->limit($page->firstRow.','.$page->listRows)->select();

        foreach($data as $k=>$v){
            $ip = $m1->where('UserID='.$v['userid'])->getField('LastLogonIP');
            //dump($v['userid']);
            //dump($ip);
            $mapip['LastLogonIP'] = $ip;
            $data[$k]['ip'] = $ip;
            $data[$k]['ip_count'] = $m1->where($mapip)->count();

            //dump( $data[$k]['ip_count']);exit;
        }

        array_multisort(array_column($data,'ip_count'),SORT_DESC,$data);
        $this->assign('data',$data);
        $this->assign('page',$show);

        $this->display();
    }

    public function roll_back(){
        $nindex = I('get.nindex');
        $m =  M('ScoreChangeDetail','','DB_RECOED');
        $m1 = M('GameScoreInfo','','DB_TREASURE');
        $m2 = M('AccountsInfo','','RDDB_USER');

//        $vip = $m2->where('usertype=4')->field('UserID')->select();
//        foreach ($vip as $k=>$v){
//            $vips[] = $v['userid'];
//        }

        if(!$nindex){
            return null;
        }

        $info = $m->where('nIndex=%d',$nindex)->select();

        //dump($info);

        $a_i = $m1->where('UserID=%d',$info[0]['userid'])->getField('InsureScore');
        $b_i = $m1->where('UserID=%d',$info[0]['userid2'])->getField('InsureScore');

        if($info[0]['type']==5){

            $this->error('该笔转账已撤回，请勿重复操作！');

        }else{

            //给转出人保险柜加上


            $add_score =  $a_i + abs($info[0]['score']);

            $data['InsureScore'] = $add_score;

            //给接收人减去

            $p_is_vip = $m2->where('UserID=%d',$info[0]['userid'])->getField('usertype');
            $r_is_vip = $m2->where('UserID=%d',$info[0]['userid2'])->getField('usertype');

            if($p_is_vip==4 && $r_is_vip==4){
                if($b_i - abs($info[0]['score'])< 0){
                    $this->error('保险柜金币不足！');exit;
                }
                $score = abs($info[0]['score']);
                $sub_score = $b_i - $score;
            }elseif ($p_is_vip!=4 && $r_is_vip!=4){
                if($b_i - abs($info[0]['score']*0.8)< 0){
                    $this->error('保险柜金币不足！');exit;
                }
                $score = abs($info[0]['score'])*0.8;
                $sub_score = $b_i - $score;
            }else{
                if($b_i - abs($info[0]['score']*0.98)< 0){
                    $this->error('保险柜金币不足！');exit;
                }
                $score = abs($info[0]['score'])*0.98;
                $sub_score = $b_i - $score;
            }


            $data2['InsureScore'] = (int)$sub_score;

            //dump($add_score);
            //dump($sub_score);exit;

            //保存修改
            $m1->where('UserID=%d',$info[0]['userid'])->save($data);
            $m1->where('UserID=%d',$info[0]['userid2'])->save($data2);

            //将此次交易标记为已经撤回


            //添加撤回金币变动记录（调转ID）
           // $data_rb = $m->where('nIndex=%d',$nindex)->select();

            $data_rb['Type'] = 5;
            $data_rb['Memo'] = '转账撤回';



            if($m->where('nIndex=%d',$nindex)->save($data_rb)){
                $user = new UserList();
                $user->do_roll_back_log($info[0]['userid'],$info[0]['userid2'],$info[0]['score'],$info[0]['changedate']);

                $this->success('撤回成功');
            }else{
                $this->error('撤回失败！');
            }


        }


    }

    public function sum_gold_change(){
        $m = M('RoomOnlineRecord','','DB_RECOED');
        $m1 = M('GameRoomItem','','DB_SERVER');
        $m2 = M('AA_ZZ_Log_PropChange');
        $uid =  I('get.userid');

        $map['userId'] = $uid;

        if(!$m->where($map)->select()){
            $data = null;
        }else{
            $count = $m->where($map)->count();
            $page = new \Think\Page($count,20);
            $show = $page->show();
            $data = $m->where($map)->limit($page->firstRow.','.$page->listRows)->select();
            foreach ($data as $k=>$v){
                $server_map['ServerID'] = $v['serverid'];
                //取得房间名

                $data[$k]['roomname'] = $m1->where($server_map)->getField('RoomName');
                //取得最后记录时间
                //$data[$k]['last_time'] = $m2->where('serverId='.$v['serverid'])->where('User_Id='.$v['userid'])->where('IsBetonGold=1')->max('LogTime');
                $data[$k]['last_time'] = $m->where('serverId='.$v['serverid'])->where($map)->max('registerDate');

                //取得总下注
                $beton_map['User_Id'] = $v['userid'];
                $beton_map['ServerId'] = $v['serverid'];
                $beton_map['IsBetonGold'] = 1;
                $data[$k]['sum_beton'] = $m2->where($beton_map)->sum('Amount');
            }
        }

        $this->assign('data',$data);
        $this->assign('page',$show);

        $this->display();

    }

    public function player_pwd(){
            $player_pwd = I('post.player_pwd');
            $userid = I('post.userid');

        if($player_pwd){
            $m = M('AccountsInfo');
            $d_id = $userid+10000;
            $data['LogonPass'] = md5($player_pwd);
            $map['UserID'] =   $userid;

            $result = $m->where($map)->save($data);
            if($result){
                $this->redirect('player_list?d_id='.$d_id);
            }else{
                echo'操作失败！';
            }


        }else{
            return null;
        }
    }

    public function insure_pwd(){
        $player_pwd = I('post.insure_pwd');
        $userid = I('post.userid');

        if($player_pwd){
            $m = M('AccountsInfo');
            $d_id = $userid+10000;
            $data['InsurePass'] = md5($player_pwd);
            $map['UserID'] =   $userid;

            $m4 = M('WebOpLog');
            $web['UserId'] = $userid;
            if(!$m4->where('UserId='.$web['UserId'])->select()){
                $m4->add($web);
            }

            $result = $m->where($map)->save($data);
            if($result){
                $this->redirect('player_list?d_id='.$d_id);
            }else{
                echo'操作失败！';
            }


        }else{
            return null;
        }
    }


    public function player_login_log(){
            $userid = I('get.userid');
            $d_id = I('get.d_id');

        if($userid || $d_id){
            $userid?$map['UserID'] =  $userid - 10000:$map['UserID'] =  $d_id;
        }else{
            $map['UserID'] =  array('gt','10000');
        }

        $m = M('RecordUserEnter','','DB_RECOED');
        $count = $m->where($map)->count();
        $page = new \Think\Page($count,15);
        $show = $page->show();
        $this->assign('page',$show);
        $data = $m->where($map)->field('UserID,ServerID,EnterTime')
            ->order('EnterTime desc')->limit($page->firstRow.','.$page->listRows)->select();

        $user = new UserList();

        if($data){
            $data = $user->login_data($data);
        }else{
            $data = null;
        }

        $this->assign('data',$data);
        $this->display();
    }

    public function player_presents_log(){

        $user = new UserList();
        $p_userid = I('get.p_userid');
        $r_userid = I('get.r_userid');
        $type = I('get.zz_type');
        $_SESSION['zz_type'] = $type;

        $p_userid?$uid = $p_userid - 10000: $uid = $r_userid - 10000;

        if($p_userid){
            $data = $user->get_received_presents(array($uid),'P',true);
        }elseif($r_userid){
            $data = $user->get_received_presents(array($uid),'R',true);
        }elseif($type=='p2p'){
            $data = $user->get_received_presents(array(),'p2p',true);
        }elseif($type=='v2v'){
            $data = $user->get_received_presents(array(),'v2v',true);
        }elseif($type=='p2v'){
            $data = $user->get_received_presents(array(),'p2v',true);
        }elseif($type=='v2p'){
            $data = $user->get_received_presents(array(),'v2p',true);
        }else{
            $data = $user->get_received_presents(array(),'all',true);
        }

        $result = $user->received_presents_data($data['data']);

        $this->assign('data',$result);
        $this->assign('page',$data['show']);

        if($p_userid){
            $this->display('present_log');
        }elseif($r_userid){
            $this->display('received_log');
        }else{
            $this->display('pr_log');
        }

    }

    public function player_presents_log_all(){
        $user = new UserList();
        $p_userid = I('get.p_userid');
        $r_userid = I('get.r_userid');

        $_SESSION['all_userid'] = $p_userid-10000;

        $type = I('get.zz_type');
        $_SESSION['zz_type'] = $type;

        $p_userid?$uid = $p_userid - 10000: $uid = $r_userid - 10000;

        $data1 = $user->get_received_presents(array($uid),'P',true);

        $data2 = $user->get_received_presents(array($uid),'R',true);

        $result1 = $user->received_presents_data($data1['data']);
        $result2 = $user->received_presents_data($data2['data']);



        $userid = I('get.p_userid');

        $uid = $userid - 10000;

        if($userid == null&&$uid==null){
            $data3 = $data4 =$data5 = null;
        }else{
            $data3 = $user->gold_change_data($uid,35);
            $data4 = $user->gold_change_data($uid,36);
            $data5 = $user->gold_change_data($uid,1);
        }



        $m = M('RoomOnlineRecord','','DB_RECOED');
        $m1 = M('GameRoomItem','','DB_SERVER');
        $m2 = M('AA_ZZ_Log_PropChange');

        $map['userId'] = $uid;

        if(!$m->where($map)->select()){
            $data = null;
        }else{
            $count = $m->where($map)->count();
            $page = new \Think\Page($count,20);
            $show = $page->show();
            $data = $m->where($map)->limit($page->firstRow.','.$page->listRows)->select();
            foreach ($data as $k=>$v){
                $server_map['ServerID'] = $v['serverid'];
                //取得房间名

                $data[$k]['roomname'] = $m1->where($server_map)->getField('RoomName');
                //取得最后记录时间
                //$data[$k]['last_time'] = $m2->where('serverId='.$v['serverid'])->where('User_Id='.$v['userid'])->where('IsBetonGold=1')->max('LogTime');
                $data[$k]['last_time'] = $m->where('serverId='.$v['serverid'])->where($map)->max('registerDate');

                //取得总下注
                $beton_map['User_Id'] = $v['userid'];
                $beton_map['ServerId'] = $v['serverid'];
                $beton_map['IsBetonGold'] = 1;
                $data[$k]['sum_beton'] = $m2->where($beton_map)->sum('Amount');
            }
        }


        $this->assign('data1',$result1);
        $this->assign('data2',$result2);

        $this->assign('page1',$data1['show']);
        $this->assign('page2',$data2['show']);

        $this->assign('data3',$data3['data']);
        $this->assign('data4',$data4['data']);
        $this->assign('data5',$data5['data']);
        $this->assign('data6',$data);


        $this->assign('page3',$data3['show']);
        $this->assign('page4',$data4['show']);
        $this->assign('page5',$data5['show']);
        $this->assign('page6',$show);


        $this->display();

    }


    public function present_log(){
        $this->display();
    }

    public function received_log(){
        $this->display();
    }

    public function player_gold_change_log(){

        $pid = I('get.pid');
        $userid = I('get.userid');
		$userid1 = I('get.userid1');
		$tid = I('get.tid');
        $user = new UserList();
        $pid?$uid = $pid: $uid = $userid - 10000;
		$userid1?$uid=$userid1:'';

        if($userid == null&&$pid==null&&$userid1==null){
            $data = null;
        }else{
            $data = $user->gold_change_data($uid,$tid);
        }
        
        $this->assign('data',$data['data']);
        $this->assign('page',$data['show']);
        $this->display();
    }

    public function player_add_gold(){
        $uid = I('post.userid');
        $d_id = $uid + 10000;
        $gold = I('post.gold');
        $remark = I('post.remark');
        $user = new UserList();
        $result = $user->add_gold($uid,$gold,$remark);

        if($result){
            $this->redirect('player_list?d_id='.$d_id);
        }else{
            echo "ERRO";
        }

    }

    public function player_add_insure(){
        $uid = I('post.userid');
        $d_id = $uid + 10000;
        $gold = I('post.gold');

        $m = M('GameScoreInfo','','DB_TREASURE');
        $m2 = M('Web_DoLog');
        $map['UserID'] = $uid;

        $before_insure =  $m->where($map)->getField('InsureScore');

        $aft_insure = $before_insure + $gold;

        $data['InsureScore'] = $aft_insure;

        $result = $m->where($map)->save($data);

        if($result){

            $data1['logName'] = $_SESSION['admin'];
            $data1['doIP'] = 0;
            $data1['res'] = '成功';
            $data1['remark'] = '为【'.$uid.'】添加【'.$gold.'】保险柜金币，添加前：【'.$before_insure.'】，添加后：【'.$aft_insure.'】';
            $data1['logType'] = 2;

            $m4 = M('WebOpLog');
            $web['UserId'] = $uid;
            if(!$m4->where('UserId='.$web['UserId'])->select()){
                $m4->add($web);
            }
            $m2->add($data1);

            $this->redirect('player_list?d_id='.$d_id);
        }else{
            echo "ERRO";
        }

    }

    public function player_ctr(){
        $uid = I('post.userid');
        $d_id = $uid + 10000;
        $cheat =I('post.cheat');
        $limit = I('post.limit');

        $user = new UserList();
        $result = $user->do_ctr($uid,$cheat,$limit);

        if($result){
            $this->redirect('player_list?d_id='.$d_id);
        }else{
            echo "ERRO";
        }

    }

    public function  player_lock(){
        $uid = I('post.userid');
        $d_id = $uid + 10000;
        $lock = I('post.lock');
        $remark = I('post.lock_remark');
        $user = new UserList();

        $result = $user->do_lock($uid,$lock,$remark);

        if($result){
            $this->redirect('player_list?d_id='.$d_id);
        }else{
            echo "ERRO";
        }
    }

    public function player_ban_deal(){
        $uid = I('post.userid');
        $d_id = $uid + 10000;
        $tid = I('post.typeid');
        $rmk = I('post.deal_remark');

        $user = new UserList();

        $result = $user->do_ban_deal($uid,$tid,$rmk);

        if($result){
            $this->redirect('player_list?d_id='.$d_id);
        }else{
            echo 'ERRO';
        }

    }

    public  function  player_change_type(){
        $uid = I('post.userid');
        $d_id = $uid + 10000;
        $usertype = I('post.usertype');
        $rmk = I('post.change_remark');

        $user = new UserList();
        $result = $user->do_change_type($uid,$usertype,$rmk);

        if($result){
            $this->redirect('player_list?d_id='.$d_id);
        }else{
            echo 'ERRO';
        }


    }

    public  function  only_v_change(){
        $uid = I('post.userid');
        $d_id = $uid + 10000;
        $usertype = I('post.usertype');
        $rmk = I('post.change_remark');

        $user = new UserList();
        $result = $user->do_change_type($uid,$usertype,$rmk);

        if($result){
            $this->redirect('only_v?userid='.$d_id);
        }else{
            echo 'ERRO';
        }


    }

    public function player_remark(){
        $uid = I('post.userid');
        $d_id = $uid + 10000;

        $rmk = I('post.player_remark');

        $user = new UserList();
        $result = $user->do_remark($uid,$rmk);

        if($result){
            $this->redirect('player_list?d_id='.$d_id);
        }else{
            echo 'ERRO';
        }
    }

    public function player_pool_gold(){
        $uid = I('post.userid');
        $d_id = $uid + 10000;

        $roomid = I('post.roomid');
        $gold = I('post.gold');

        $user = new UserList();

        $dx_num = I('post.dx_num');

        dump($uid.'-'.$roomid.'-'.$gold);

        if($dx_num){
            $result = $user->do_pool_gold23($uid,$roomid,$gold,$dx_num);
        }else{
            $result = $user->do_pool_gold($uid,$roomid,$gold);
        }

        dump($dx_num);
        dump($result);


        if($result){
            $this->redirect('player_list?d_id='.$d_id);
        }else{
            echo 'ERRO';
        }

    }
	
	public function player_deal_list(){

		if(I('get.stime') && I('get.etime')){

            if(I('get.sort')){
                $stime = I('get.stime');
                $etime = I('get.etime');
            }else{
                $stime = date('Y-m-d',I('get.stime'))  ;
                $etime = date('Y-m-d',I('get.etime'))  ;
            }
			
        }else{

            $stime = '';
            $etime = '';
        }

		$sort = I('get.sort',0);

        $page_count = I('get.page_count',500);

        $type = I('get.pr_type','R');
		
		$user = new UserList();

		$data = $user->deal_data('',$stime,$etime,$type,$sort,$page_count);
		
		$this->assign('data',$data['data']);
		$this->assign('date',$data['date']);
        $this->assign('title',$data['title']);
        $this->assign('page',$data['show']);

		$this->display();
		
	}

	public function player_win_data(){

            $stime = I('get.stime',0);
            $etime = I('get.etime',0);

        if($stime && $etime){
            $_SESSION['stime'] = date('Y-m-d',$stime)  ;
            $_SESSION['etime'] = date('Y-m-d',$etime)  ;

        }else{
            $_SESSION['stime'] ='';
            $_SESSION['etime'] ='';
        }

        $serverid = I('get.serverid');

        $serverid?$_SESSION['sid'] = $serverid:$sid=$_SESSION['sid'];

        $_SESSION['sort'] = I('get.sort','today_win');

        $user = new UserList();

        $data = $user->win_data($_SESSION['sid'],$_SESSION['stime'],$_SESSION['etime'],$_SESSION['sort']);

        $map['stime'] = $_SESSION['stime'];
        $map['etime'] = $_SESSION['etime'];
        $map['sid'] = $_SESSION['sid'];

        $this->assign('data',$data['data']);
        $this->assign('page',$data['show']);
        $this->assign('map',$map);

        $this->display();
    }

    public function player_msg(){

        $uid = I('get.userid','');
        $stime = I('get.stime');
        $etime = I('get.etime');

        if($stime && $etime){
            $stime = date('Y-m-d',$stime)  ;
            $etime = date('Y-m-d',$etime)  ;

        }else{
            $stime = '';
            $etime = '';
        }

        $user = new UserList();

        $data = $user->message($uid,$stime,$etime);
        $this->assign('data',$data['data']);
        $this->assign('page',$data['show']);
        $this->display();
    }

    public function player_msg_detail(){

        $uid = I('get.userid','');

        $user = new UserList();
        $data = $user->msg_detail($uid);
        $this->assign('data',$data);
        $this->display();
    }

    public  function  add_reply(){

        $uid = I('post.userid','');
        $reply = I('post.content');
        $user = new UserList();

        $result = $user->add_reply($uid,$reply);

        if($result){
            $this->redirect('player_msg_detail?userid='.$uid);
        }else{
            echo 'ERRO';
        }
    }
	
	public function mobile_relation(){
		 $m = M('AccountsInfo');

		 $mb_num = I('get.mb_num');

		 if($mb_num){
             $map['PhoneNum'] = $mb_num;
             $data = $m->where($map)->select();
             foreach ($data as $v){
                 $uids[] = $v['userid'];
             }
             $user = new UserList();
             $log_win = $user->get_sum_win($uids,true);
             foreach ($data as $k=>$v){
                 $data[$k]['log_win'] =  $log_win[$v['userid']]['realscore'];
             }
         }else{
             $data = null;
         }



		 $this->assign('data',$data);
		 $this->display();
	}

	public function ip_relation(){
		 $m = M('AccountsInfo');

		 $ip = I('get.ip');

		 if($ip){
             $map['LastLogonIP'] = $ip;

             $count = $m->where($map)->count();
             $page = new \Think\Page($count,30);
             $show = $page->show();
             $data = $m->where($map)->limit($page->firstRow.','.$page->listRows)->select();

         }else{
             $data = null;
         }

        foreach ($data as $k=>$v){
            $uids[] = $v['userid'];
        }
        $user = new UserList();
        $received_sum = $user->get_received_presents($uids,'R');
        $log_win = $user->get_sum_win($uids,true);
         foreach ($data as $k=>$v){
             $data[$k]['received_sum'] = $received_sum[$v['userid']]['total']?abs($received_sum[$v['userid']]['total']):0;
             $data[$k]['log_win'] = $log_win[$v['userid']]['realscore'];
         }

        $this->assign('data',$data);
        $this->assign('page',$show);

        $this->display();
	}
	public function code_relation(){
		 $m = M('AccountsInfo');

		 $code = I('get.code');

		 if($code){
             $map['MachineSerial'] = $code;
             $data = $m->where($map)->select();
         }else{
             $data = null;
         }
		 $this->assign('data',$data);
		 $this->display();
	}

	public function lot_code_rmk(){
            $rmk = $_POST['rmk'];
            $uid = $_POST['userid'];
        $m = M('AccountsInfo');
        $m1 = M('AccountsInfoExtend');

        $code = I('post.code');
        $map1['MachineSerial'] = $code;
        $map1['UserID'] = array('gt',10000);
        $uids = $m->where($map1)->field('UserID')->select();

        for ($i=0;$i<count($uids);$i++){
            $map['UserID'] = $uids[$i]['userid'];

            if($m1->where($map)->find()){
                $save['Remark'] = $rmk;
                $m1->where($map)->save($save);

            }else{

                $map['Remark'] = $rmk;

                $m1->add($map);
            }

        }
        $d_id = $uid+10000;
        $this->redirect('player_list?d_id='.$d_id);
    }

    public function lot_phone_rmk(){
        $rmk = $_POST['rmk'];
        $uid = $_POST['userid'];
        $m = M('AccountsInfo');
        $m1 = M('AccountsInfoExtend');

        $phone = I('post.phone');
        $map1['PhoneNum'] = $phone;
        $map1['UserID'] = array('gt',10000);
        $uids = $m->where($map1)->field('UserID')->select();

       // dump($uids);exit;

        for ($i=0;$i<count($uids);$i++){
            $map['UserID'] = $uids[$i]['userid'];

            if($m1->where($map)->find()){
                $save['Remark'] = $rmk;
                $m1->where($map)->save($save);

            }else{

                $map['Remark'] = $rmk;

                $m1->add($map);
            }

        }
        $d_id = $uid+10000;
        $this->redirect('player_list?d_id='.$d_id);
    }

    public function lot_ip_rmk(){
        $rmk = $_POST['rmk'];
        $uid = $_POST['userid'];
        $m = M('AccountsInfo');
        $m1 = M('AccountsInfoExtend');

        $ip = I('post.ip');
        $map1['LastLogonIP'] = $ip;
        $map1['UserID'] = array('gt',10000);
        $uids = $m->where($map1)->field('UserID')->select();

        for ($i=0;$i<count($uids);$i++){
            $map['UserID'] = $uids[$i]['userid'];

            if($m1->where($map)->find()){
                $save['Remark'] = $rmk;
                $m1->where($map)->save($save);

            }else{

                $map['Remark'] = $rmk;

                $m1->add($map);
            }

        }
        $d_id = $uid+10000;
        $this->redirect('player_list?d_id='.$d_id);
    }

    public function lot_code_lock(){
        $m = M('AccountsInfo');
        $m1 = M('AA_ban_deal_account');
        $m2 = M('Web_DoLog');
        $code = I('get.code');
        $uid =I('get.uid');;

        $map1['MachineSerial'] = $code;
        $map1['UserID'] = array('gt',10000);
        $uids = $m->where($map1)->field('UserID')->select();

        //dump($uids);
      //  dump($map1);exit;
        for ($i=0;$i<count($uids);$i++){

            $add['user_id'] = $uids[$i]['userid'];
            if(!$m1->where('user_id='. $add['user_id'])->find()){
                $m1->add($add);
            }

        }

        $data1['logName'] = $_SESSION['admin'];
        $data1['doIP'] = 0;
        $data1['res'] = '成功';
        $data1['remark'] = '批量禁止交易与机器码【'.$code.'】关联的账号';
        $data1['logType'] = 4;
        $m2->add($data1);

        $d_id = $uid+10000;
        $this->redirect('player_list?d_id='.$d_id);

    }
    public function lot_code_unlock(){
        $m = M('AccountsInfo');
        $m1 = M('AA_ban_deal_account');
        $m2 = M('Web_DoLog');
        $code = I('get.code');
        $uid =I('get.uid');;

        $map1['MachineSerial'] = $code;
        $map1['UserID'] = array('gt',10000);
        $uids = $m->where($map1)->field('UserID')->select();

        //dump($uids);
        //  dump($map1);exit;
        for ($i=0;$i<count($uids);$i++){

            $add['user_id'] = $uids[$i]['userid'];
            if($m1->where('user_id='. $add['user_id'])->find()){
                $m1->where('user_id='.$add['user_id'])->delete();
            }

        }

        $data1['logName'] = $_SESSION['admin'];
        $data1['doIP'] = 0;
        $data1['res'] = '成功';
        $data1['remark'] = '批量解除禁止交易与机器码【'.$code.'】关联的账号';
        $data1['logType'] = 4;
        $m2->add($data1);

        $d_id = $uid+10000;
        $this->redirect('player_list?d_id='.$d_id);

    }


    public function lot_ip_lock(){
        $m = M('AccountsInfo');
        $m1 = M('AA_ban_deal_account');
        $m2 = M('Web_DoLog');
        $ip = I('get.code');
        $uid =I('get.uid');

        $map1['LastLogonIP'] = $ip;
        $map1['UserID'] = array('gt',10000);
        $uids = $m->where($map1)->field('UserID')->select();

        //dump($ip);exit;
        for ($i=0;$i<count($uids);$i++){

            $add['user_id'] = $uids[$i]['userid'];
            if(!$m1->where('user_id='. $add['user_id'])->find()){
               $m1->add($add);
            }

        }

        $data1['logName'] = $_SESSION['admin'];
        $data1['doIP'] = 0;
        $data1['res'] = '成功';
        $data1['remark'] = '批量禁止交易与登录IP【'.$ip.'】关联的账号';
        $data1['logType'] = 4;
        $m2->add($data1);

        $d_id = $uid+10000;
        $this->redirect('player_list?d_id='.$d_id);
    }

    public function lot_ip_unlogin(){
        $m = M('AccountsInfo');
        $m2 = M('Web_DoLog');
        $ip = I('get.ip');
        $uid =I('get.uid');
        $map1['LastLogonIP'] = $ip;
        $map1['UserID'] = array('gt',10000);
        $uids = $m->where($map1)->field('UserID')->select();
        $save['LimitLogin'] = 1;

        foreach($uids as $v){
            $m->where('UserID='.$v['userid'])->save($save);
        }

        $data1['logName'] = $_SESSION['admin'];
        $data1['doIP'] = 0;
        $data1['res'] = '成功';
        $data1['remark'] = '批量禁止登录与登录IP【'.$ip.'】关联的账号';
        $data1['logType'] = 4;


        if($m2->add($data1)){
            $this->success('操作成功！');
        }else{
            $this->error('操作失败！');
        }

    }

    public function lot_ip_dologin(){
        $m = M('AccountsInfo');
        $m2 = M('Web_DoLog');
        $ip = I('get.ip');
        $uid =I('get.uid');
        $map1['LastLogonIP'] = $ip;
        $map1['UserID'] = array('gt',10000);
        $uids = $m->where($map1)->field('UserID')->select();
        $save['LimitLogin'] = 0;

        foreach($uids as $v){
            $m->where('UserID='.$v['userid'])->save($save);
        }

        $data1['logName'] = $_SESSION['admin'];
        $data1['doIP'] = 0;
        $data1['res'] = '成功';
        $data1['remark'] = '批量解除登录与登录IP【'.$ip.'】关联的账号';
        $data1['logType'] = 4;


        if($m2->add($data1)){
            $this->success('操作成功！');
        }else{
            $this->error('操作失败！');
        }
    }

    public function lot_ip_unlock(){
        $m = M('AccountsInfo');
        $m1 = M('AA_ban_deal_account');
        $m2 = M('Web_DoLog');
        $ip = I('get.code');
        $uid =I('get.uid');

        $map1['LastLogonIP'] = $ip;
        $map1['UserID'] = array('gt',10000);
        $uids = $m->where($map1)->field('UserID')->select();

        //dump($map1);exit;
        for ($i=0;$i<count($uids);$i++){

            $add['user_id'] = $uids[$i]['userid'];
            if($m1->where('user_id='. $add['user_id'])->find()){
                $m1->where('user_id='.$add['user_id'])->delete();
            }

        }

        $data1['logName'] = $_SESSION['admin'];
        $data1['doIP'] = 0;
        $data1['res'] = '成功';
        $data1['remark'] = '批量解除禁止交易与登录IP【'.$ip.'】关联的账号';
        $data1['logType'] = 4;
        $m2->add($data1);

        $d_id = $uid+10000;
        $this->redirect('player_list?d_id='.$d_id);
    }


    public function lot_phone_lock(){
        $m = M('AccountsInfo');
        $code = I('get.phone');
        $uid =I('get.uid');;

        $map1['PhoneNum'] = $code;
        $map1['UserID'] = array('gt',10000);
        $uids = $m->where($map1)->field('UserID')->select();
       // dump($map1);exit;
        for ($i=0;$i<count($uids);$i++){
            $map['UserID'] = $uids[$i]['userid'];
            $save['LimitLogin'] = 1;
            $m->where($map)->save($save);
        }
        $d_id = $uid+10000;
        $this->redirect('player_list?d_id='.$d_id);

    }



}