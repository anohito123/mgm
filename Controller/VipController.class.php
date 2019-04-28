<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Logic\VipList;
use Admin\Logic\UserList;

header('Content-Type:text/html; charset=utf-8');
class VipController extends BaseController {

    //Vip列表
    public function viplist(){

        if($_POST['stime']&&$_POST['etime']){
            $stime = date('Y-m-d',$_POST['stime'])  ;
            $etime = date('Y-m-d',$_POST['etime'])  ;
        }else{
            $stime = '';
            $etime = '';
        }

        $_POST['deal_type']?$type=$_POST['deal_type']:$type='player';


        $viplist = new VipList();

        $data = $viplist->vip_data($stime,$etime,$type,1000);

        $this->assign('page',$data['show']);
        $this->assign('data',$data['data']);
        $this->assign('date',$data['date']);
        $this->assign('title',$data['title']);

        $this->display();

    }

    public function vip_ctr(){

        $viplist = new VipList();
        $m1 = M('Vip_Ctrl_Config','','RDDB_USER');
        $cnf = $m1->select();
        $data = $viplist->vip_data1('','','player',15);


        $this->assign('cnf',$cnf);
        $this->assign('page',$data['show']);
        $this->assign('data',$data['data']);

        $this->display();
    }

    public function vip_ctrl_update(){
        $m = M('Vip_Ctrl_Config','','RDDB_USER');

        $big_x = (float)I('get.big_x');
        $big_cheat = (int)I('get.big_cheat');
        $big_y = (float)I('get.big_y');
        $gt_sumwin = (int)I('get.gt_sumwin');
        $lt_sumwin = (int)I('get.lt_sumwin');
        $small1_x = (float)I('get.small1_x');
        $small2_x = (float)I('get.small2_x');
        $small1_cheat = (int)I('get.small1_cheat');
        $small2_cheat = (int)I('get.small2_cheat');
        $small2_y = (float)I('get.small2_y');


        if(is_float($big_x) && is_float($big_y) && is_float($small1_x) && is_float($small2_x) && is_float($small2_y)){
            $result = $m->query("declare @count int exec @count = [dbo].[Web_Update_Vip_Ctrl] "."'".$big_x."'".",'".$big_cheat."','".$big_y."','".
                $gt_sumwin."','".$lt_sumwin."','".$small1_x."','".$small2_x."','".$small1_cheat."','".$small2_cheat."','".$small2_y."' select @count");

            $this->success('修改成功！');
        }else{
            $this->error('参数有误！');
        }



    }

    public function do_vip_ctr(){
        $m = M('Web_Auto_List','','RDDB_USER');

        $userid = I('get.userid');
        $flag = I('get.flag');

        $result = $m->query("declare @count int exec @count = [dbo].[VipCtr] "."'".$userid."'".",'".$flag."' select @count");

        $this->success('点控已生效！');
    }

    public function vip_deal_user(){

        $m = M('AccountsInfo');
        $m1 =  M('ScoreChangeDetail','','DB_RECOED');
        $m2 = M('GameScoreLocker','','DB_TREASURE');
        $m3 = M('GameRoomItem','','DB_SERVER');

        $map['usertype'] = 4;

        $users = S('usertype');
        if(empty($users)){
            $users = $m->where($map)->field('UserID,NickName,RegisterDate,EndCheatDate,usertype')->select();
            S('usertype',$users,1200);
        }


        //获取玩家ID
        foreach ($users as $k=>$v){
            $uids[] = $v['userid'];
        }

        $map['ChangeDate'] = array('between',$_GET['stime'].','.$_GET['etime']);

        $r_vipid = I('get.r_vipid');
        $p_vipid = I('get.p_vipid');


        if($r_vipid){
            $map['UserID'] = array('not in',$uids);
            $map['UserId2'] = $r_vipid;
            $users = $m1->where($map)->field('distinct UserID')->select();
            foreach ($users as $k=>$v){
                $deal_uids[] = $v['userid'];
            }

        }elseif($p_vipid){
            $map['UserId2'] = array('not in',$uids);
            $map['UserID'] = $p_vipid;
            $users = $m1->where($map)->field('distinct UserId2')->select();
            foreach ($users as $k=>$v){
                $deal_uids[] = $v['userid2'];
            }
        }

        $dmap['UserID'] = array('in',$deal_uids);


        $deal_users = S('deal_users');

        if(empty($deal_users)){
            $deal_users = $m->where($dmap)->select();
            $user = new UserList();
            $remark = $user->get_remark($deal_uids);
            $log_win = $user->get_sum_win($deal_uids,true);
            $today_win = $user->get_sum_win($deal_uids);
            $received_sum = $user->get_received_presents($deal_uids,'R');
            $present_sum = $user->get_received_presents($deal_uids,'P');

            $today_r = $user->get_received_presents($deal_uids,'R',false,array(),false,true);
            $today_p = $user->get_received_presents($deal_uids,'P',false,array(),false,true);

            $sum_gold = $user->get_sum_gold($deal_uids);
            $insure_gold = $user->get_sum_win($deal_uids,false,true);

            $ex_date = $user->get_exchange_field($deal_uids,'ex_date');
            $ex_count = $user->get_exchange_field($deal_uids,'ex_count');

            $deal1_r = $user->get_deal_field($deal_uids,'R',1);
            $deal2_r = $user->get_deal_field($deal_uids,'R',2);
            $deal3_r = $user->get_deal_field($deal_uids,'R',3);

            $deal1_p = $user->get_deal_field($deal_uids,'P',1);
            $deal2_p = $user->get_deal_field($deal_uids,'P',2);
            $deal3_p = $user->get_deal_field($deal_uids,'p',3);

            $cheat = $user->get_cheat_limit($deal_uids);
            $cheat_limit = $user->get_cheat_limit($deal_uids,'limit');
            $cheat_blood = $user->get_cheat_limit($deal_uids,'blood');

            foreach ($deal_users as $k=>$v){
                $rid = $m2->where('UserID='.$v['userid'])->getField('ServerID');
                $roomname = $m3->where('ServerID='.$rid)->getField('RoomName');

                $deal_users[$k]['remark'] = $remark[$v['userid']]['remark']?$remark[$v['userid']]['remark']:'--';
                $deal_users[$k]['current_room'] = $roomname?$roomname:'--';
                $deal_users[$k]['log_win'] = $log_win[$v['userid']]['realscore'];
                $deal_users[$k]['today_win'] = $today_win[$v['userid']]['day_income']?$today_win[$v['userid']]['day_income']:0;
                $deal_users[$k]['received_sum'] = $received_sum[$v['userid']]['total']?abs($received_sum[$v['userid']]['total']):0;
                $deal_users[$k]['present_sum'] = $present_sum[$v['userid']]['total']?abs($present_sum[$v['userid']]['total']):0;
                $deal_users[$k]['sum_ab'] = $deal_users[$k]['received_sum'] - $deal_users[$k]['present_sum'];

                $deal_users[$k]['today_r_sum'] = $today_r[$v['userid']]['total']?abs($today_r[$v['userid']]['total']):0;
                $deal_users[$k]['today_p_sum'] = $today_p[$v['userid']]['total']?abs($today_p[$v['userid']]['total']):0;
                $deal_users[$k]['today_ab'] = $deal_users[$k]['today_r_sum'] - $deal_users[$k]['today_p_sum'];
                $deal_users[$k]['sum_gold'] = $sum_gold[$v['userid']]['amount'];
                $deal_users[$k]['insure_gold'] = $insure_gold[$v['userid']]['insurescore'];

                $deal_users[$k]['ex_date'] = $ex_date[$v['userid']]['exdate'];
                $deal_users[$k]['ex_count'] = $ex_count[$v['userid']]['total'];

                $deal_users[$k]['deal1_r_date'] = $deal1_r[$v['userid']]['changedate'];
                $deal_users[$k]['deal1_r_score'] = $deal1_r[$v['userid']]['score'];
                $deal_users[$k]['deal2_r_date'] = $deal2_r[$v['userid']]['changedate'];
                $deal_users[$k]['deal2_r_score'] = $deal2_r[$v['userid']]['score'];
                $deal_users[$k]['deal3_r_date'] = $deal3_r[$v['userid']]['changedate'];
                $deal_users[$k]['deal3_r_score'] = $deal3_r[$v['userid']]['score'];

                $deal_users[$k]['deal1_p_date'] = $deal1_p[$v['userid']]['changedate'];
                $deal_users[$k]['deal1_p_score'] = $deal1_p[$v['userid']]['score'];
                $deal_users[$k]['deal2_p_date'] = $deal2_p[$v['userid']]['changedate'];
                $deal_users[$k]['deal2_p_score'] = $deal2_p[$v['userid']]['score'];
                $deal_users[$k]['deal3_p_date'] = $deal3_p[$v['userid']]['changedate'];
                $deal_users[$k]['deal3_p_score'] = $deal3_p[$v['userid']]['score'];

                $deal_users[$k]['cheat'] = $cheat[$v['userid']]['cheatrate'];
                $deal_users[$k]['limit'] = $cheat_limit[$v['userid']]['limitscore'];
                $deal_users[$k]['blood'] = $cheat_blood[$v['userid']]['bloodscore'];


                if( $deal_users[$k]['cheat']>0){
                    $c_limit = 0 - $deal_users[$k]['limit'];
                }else{
                    $c_limit = $deal_users[$k]['limit'];
                }

                $deal_users[$k]['sp'] = abs($c_limit - $deal_users[$k]['blood']);

            }
            S('deal_users',$deal_users,120);
        }

        $this->assign('data',$deal_users);
        $this->display();

    }
}