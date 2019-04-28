<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/5
 * Time: 14:53
 */

namespace Admin\Logic;
use Admin\Logic\UserList;


class VipList extends BaseLogic{

    public function vip_data($stime='',$etime='', $type='player',$page_count){

        if($type=='player'){
            $_SESSION['deal_type'] = 'player';
        }elseif ($type=='vip'){
            $_SESSION['deal_type'] = 'vip';
        }else{
            $_SESSION['deal_type'] = 'all';
        }

        $today = date('Y-m-d');
        $tomorrow = date("Y-m-d",strtotime("+1 day"));

        if($stime==''||$etime==''){
            $stime = $today;
            $etime = $tomorrow;
        }

        $m = M('AccountsInfo');
        $m1 = M('GameScoreInfo','','DB_TREASURE');
        $ul = new UserList();
        $map['usertype'] = 4;

        $count = count($m->where($map)->field('UserID')->select());
        $page = new \Think\Page($count,$page_count);
        $users = $m->where($map)->field('UserID,NickName,RegisterDate')
            ->limit($page->firstRow.','.$page->listRows)
            ->select();

        //获取玩家ID
        foreach ($users as $k=>$v){
            $uids[] = $v['userid'];
        }

        $sum_gold = $ul->get_sum_gold($uids);
        $remark = $ul->get_remark($uids);
        $insure_gold = $ul->get_sum_win($uids,false,true);

        //总赠送
        $sum_present = $this->vip_pr($uids,$type,'P',$stime,$etime);

        //总赠送笔数
        $sum_present_times = $this->vip_pr($uids,$type,'P',$stime,$etime,'times');

        //总赠送人数
        $sum_present_person = $this->vip_pr($uids,$type,'P',$stime,$etime,'person');

        //总接收
        $sum_received = $this->vip_pr($uids,$type,'R',$stime,$etime);


        //总接收笔数
        $sum_received_times = $this->vip_pr($uids,$type,'R',$stime,$etime,'times');

        //总接收人数
        $sum_received_person = $this->vip_pr($uids,$type,'R',$stime,$etime,'person');


        $title['t_vips'] = count($uids);

        $map_si['UserID'] = array('in',$uids);

        $title['sum_insure'] = $m1->where($map_si)->sum('InsureScore');

        //总税收
        $title['t_all_tax'] = abs($this->vip_pr($uids,'player','P',$stime,$etime,'sum_present'))*0.02;

        foreach ($users as $k=>$v){

            $users[$k]['sum_gold'] = $sum_gold[$v['userid']]['amount'];
            $title['t_gold']+=$users[$k]['sum_gold'];
            $users[$k]['remark'] = $remark[$v['userid']]['remark']?$remark[$v['userid']]['remark']:'--';
            $users[$k]['insure_gold'] = $insure_gold[$v['userid']]['insurescore'];
            $users[$k]['sum_present'] = $sum_present[$v['userid']]['total']?abs($sum_present[$v['userid']]['total']):0;

            if($type=='player'){
                $users[$k]['sum_tax'] = abs($users[$k]['sum_present'])*0.02;
            }else{
                $users[$k]['sum_tax'] = 0;
            }


            $title['t_pgold']+=$users[$k]['sum_present'];

            $users[$k]['sum_received'] = $sum_received[$v['userid']]['total']?abs($sum_received[$v['userid']]['total']):0;
            $title['t_rgold']+=$users[$k]['sum_received'];

            $users[$k]['sum_present_times'] = $sum_present_times[$v['userid']]['total']?$sum_present_times[$v['userid']]['total']:0;
            $title['t_ptimes']+=$users[$k]['sum_present_times'];

            $users[$k]['sum_present_person'] = $sum_present_person[$v['userid']]['total']?$sum_present_person[$v['userid']]['total']:0;
            $title['t_pperson']+=$users[$k]['sum_present_person'];

            $users[$k]['sum_received_times'] = $sum_received_times[$v['userid']]['total']?$sum_received_times[$v['userid']]['total']:0;
            $title['t_rtimes']+=$users[$k]['sum_received_times'];

            $users[$k]['sum_received_person'] = $sum_received_person[$v['userid']]['total']?$sum_received_person[$v['userid']]['total']:0;
            $title['t_rperson']+=$users[$k]['sum_received_person'];

            $users[$k]['ab'] = abs($users[$k]['sum_present'])-abs($users[$k]['sum_received']);

            $title['t_ab']+=$users[$k]['ab'];

            $users[$k]['sum_times'] = $users[$k]['sum_present_times'] + $users[$k]['sum_received_times'] ;
            $title['t_sum_times'] += $users[$k]['sum_times'];

            $users[$k]['sum_person'] = $users[$k]['sum_present_person'] + $users[$k]['sum_received_person'] ;
            $title['t_sum_person'] += $users[$k]['sum_person'];
        }

        $date['stime'] = $stime;
        $date['etime'] = $etime;

        $result['data'] = $users;
        $result['date'] = $date;
        $result['title'] = $title;

        $result['show'] = $page->show();

        return $result;

    }


    public function vip_data1($stime='',$etime='', $type='player',$page_count){

        if($type=='player'){
            $_SESSION['deal_type'] = 'player';
        }elseif ($type=='vip'){
            $_SESSION['deal_type'] = 'vip';
        }else{
            $_SESSION['deal_type'] = 'all';
        }

        $today = date('Y-m-d');
        $tomorrow = date("Y-m-d",strtotime("+1 day"));

        if($stime==''||$etime==''){
            $stime = $today;
            $etime = $tomorrow;
        }

        $m = M('AccountsInfo');
        $m1 = M('GameScoreInfo','','DB_TREASURE');
        $ul = new UserList();
        $map['usertype'] = 4;

        //$count = count($m->where($map)->field('UserID')->select());
        //$page = new \Think\Page($count,$page_count);
        $users = $m->where($map)->field('UserID,NickName,RegisterDate')
            //->limit($page->firstRow.','.$page->listRows)
            ->select();

        //获取玩家ID
        foreach ($users as $k=>$v){
            $uids[] = $v['userid'];
        }
		

		
        $remark = $ul->get_remark($uids);


        //总赠送
        $sum_present = $this->vip_pr1($type,'P',date("Y-m-d",strtotime("-1 year")),$etime);

        //总赠送笔数
        $sum_present_times = $this->vip_pr1($type,'P',date("Y-m-d",strtotime("-1 year")),$etime,'times');


        //总赠送人数
        $sum_present_person = $this->vip_pr1($type,'P',date("Y-m-d",strtotime("-1 year")),$etime,'person');

        //总接收
        $sum_received = $this->vip_pr1($type,'R',date("Y-m-d",strtotime("-1 year")),$etime);


        //总接收笔数
        $sum_received_times = $this->vip_pr1($type,'R',date("Y-m-d",strtotime("-1 year")),$etime,'times');

        //总接收人数
        $sum_received_person = $this->vip_pr1($type,'R',date("Y-m-d",strtotime("-1 year")),$etime,'person');

        //新加字段------------------------------------

        //近一周总赠送
        $sum_p_7 = $this->vip_pr1($type,'P',date("Y-m-d",strtotime("-7 day")),$etime);

        //近一周总接收
        $sum_r_7 = $this->vip_pr1($type,'R',date("Y-m-d",strtotime("-7 day")),$etime);


        //近一周赠送笔数
        $sum_p_times_week = $this->vip_pr1($type,'P',date("Y-m-d",strtotime("-7 day")),$etime,'times');

        //近一周接收笔数
        $sum_r_times_week = $this->vip_pr1($type,'R',date("Y-m-d",strtotime("-7 day")),$etime,'times');

        //上一周总赠送
        $sum_p_14 = $this->vip_pr1($type,'P',date("Y-m-d",strtotime("-14 day")),date("Y-m-d",strtotime("-7 day")),$etime);

        //上一周总接收
        $sum_r_14 = $this->vip_pr1($type,'R',date("Y-m-d",strtotime("-14 day")),date("Y-m-d",strtotime("-7 day")),$etime);

        //上一周赠送笔数
        $sum_p_times_last_week = $this->vip_pr1($type,'P',date("Y-m-d",strtotime("-14 day")),date("Y-m-d",strtotime("-7 day")),'times');

        //上一周接收笔数
        $sum_r_times_last_week = $this->vip_pr1($type,'R',date("Y-m-d",strtotime("-14 day")),date("Y-m-d",strtotime("-7 day")),'times');



        foreach ($users as $k=>$v){

            $users[$k]['remark'] = $remark[$v['userid']]['remark']?$remark[$v['userid']]['remark']:'--';
            $users[$k]['sum_present'] = $sum_present[$v['userid']]['total']?abs($sum_present[$v['userid']]['total']):0;
            $users[$k]['sum_received'] = $sum_received[$v['userid']]['total']?abs($sum_received[$v['userid']]['total']):0;
            $users[$k]['sum_present_times'] = $sum_present_times[$v['userid']]['total']?$sum_present_times[$v['userid']]['total']:0;
            $users[$k]['sum_present_person'] = $sum_present_person[$v['userid']]['total']?$sum_present_person[$v['userid']]['total']:0;
            $users[$k]['sum_received_times'] = $sum_received_times[$v['userid']]['total']?$sum_received_times[$v['userid']]['total']:0;
            $users[$k]['sum_received_person'] = $sum_received_person[$v['userid']]['total']?$sum_received_person[$v['userid']]['total']:0;
            $users[$k]['ab'] = abs($users[$k]['sum_present'])-abs($users[$k]['sum_received']);
            $users[$k]['sum_times'] = $users[$k]['sum_present_times'] + $users[$k]['sum_received_times'] ;
            $users[$k]['sum_person'] = $users[$k]['sum_present_person'] + $users[$k]['sum_received_person'] ;


            $users[$k]['sum_p_7'] = $sum_p_7[$v['userid']]['total']?abs($sum_p_7[$v['userid']]['total']):0;
            $users[$k]['sum_r_7'] = $sum_r_7[$v['userid']]['total']?abs($sum_r_7[$v['userid']]['total']):0;
            $users[$k]['sum_7_ab'] = $users[$k]['sum_p_7'] - $users[$k]['sum_r_7'];

            $users[$k]['sum_p_14'] = $sum_p_14[$v['userid']]['total']?abs($sum_p_14[$v['userid']]['total']):0;
            $users[$k]['sum_r_14'] = $sum_r_14[$v['userid']]['total']?abs($sum_r_14[$v['userid']]['total']):0;
            $users[$k]['sum_14_ab'] = $users[$k]['sum_p_14'] - $users[$k]['sum_r_14'];

            $users[$k]['sum_p_times_week'] = $sum_p_times_week[$v['userid']]['total']?$sum_p_times_week[$v['userid']]['total']:0;
            $users[$k]['sum_r_times_week'] = $sum_r_times_week[$v['userid']]['total']?$sum_r_times_week[$v['userid']]['total']:0;

            $users[$k]['sum_p_times_last_week'] = $sum_p_times_last_week[$v['userid']]['total']?$sum_p_times_last_week[$v['userid']]['total']:0;
            $users[$k]['sum_r_times_last_week'] = $sum_r_times_last_week[$v['userid']]['total']?$sum_r_times_last_week[$v['userid']]['total']:0;

        }
		
		array_multisort(array_column($users,'sum_7_ab'),SORT_DESC,$users);

        $result['data'] = $users;
        //$result['show'] = $page->show();
        return $result;

    }

    public function vip_pr($uids = array(),$type,$key,$stime='',$etime='',$countType='gold'){

        $map['ChangeDate'] = array('between',$stime.','.$etime);

        $m1 =  M('ScoreChangeDetail','','DB_RECOED');

            if($key=='P'){
                $map['UserID'] = array('in',$uids);

                if($type=='player'){
                    $map['UserId2'] = array('not in',$uids);
                }elseif ($type=='vip'){
                    $map['UserId2'] = array('in',$uids);
                }

                if($countType=='times'){
                    $data = $m1->where($map)->where('UserID!=UserId2')->field('UserID,count(UserID) as total')->group('UserID')->select();
                }elseif ($countType=='person'){
                    $data = $m1->where($map)->where('UserID!=UserId2')->field('UserID,count(distinct UserId2) as total')->group('UserID')->select();
                }elseif ($countType=='sum_present'){
                    $data = $m1->where($map)->where('UserID!=UserId2')->sum('Score');
                    return $data;
                }else{
                    $data = $m1->where($map)->where('UserID!=UserId2')->field('UserID,sum(Score) as total')->group('UserID')->select();
                }

                $data = $this->changeKeys($data, 'userid');

            }elseif ($key=='R'){

                $map['UserId2'] = array('in',$uids);

                if($type=='player'){
                    $map['UserID'] = array('not in',$uids);
                }elseif ($type=='vip'){
                    $map['UserID'] = array('in',$uids);
                }

                if($countType=='times'){
                    $data = $m1->where($map)->where('UserID!=UserId2')->field('UserId2,count(UserId2) as total')->group('UserId2')->select();
                }elseif ($countType=='person'){
                    $data = $m1->where($map)->where('UserID!=UserId2')->field('UserId2,count(distinct UserID) as total')->group('UserId2')->select();
                }else{
                    $data = $m1->where($map)->where('UserID!=UserId2')->field('UserId2,sum(Score) as total')->group('UserId2')->select();
                }

                $data = $this->changeKeys($data, 'userid2');
            }

        return $data;
    }
	
	public function vip_pr1($type,$key,$stime='',$etime='',$countType='gold'){

        $m = M('AccountsInfo');

        $map['ChangeDate'] = array('between',$stime.','.$etime);

		
		$vips = S('vips_s');
            if(empty($vips)){
                $vip = $m->where('usertype=4')->select();
				foreach ($vip as $k=>$v){
					$vips[] = $v['userid'];
				}
                S('vips_s',$vips,9000);
            }

       

        $m1 =  M('ScoreChangeDetail','','DB_RECOED');

            if($key=='P'){
                $map['UserID'] = array('in',$vips);

                if($type=='player'){
                    $map['UserId2'] = array('not in',$vips);
                }elseif ($type=='vip'){
                    $map['UserId2'] = array('in',$vips);
                }

                if($countType=='times'){
                    $data = $m1->where($map)->where('UserID!=UserId2')->field('UserID,count(UserID) as total')->group('UserID')->select();
                }elseif ($countType=='person'){
                    $data = $m1->where($map)->where('UserID!=UserId2')->field('UserID,count(distinct UserId2) as total')->group('UserID')->select();
                }elseif ($countType=='sum_present'){
                    $data = $m1->where($map)->where('UserID!=UserId2')->sum('Score');
                    return $data;
                }else{
                    $data = $m1->where($map)->where('UserID!=UserId2')->field('UserID,sum(Score) as total')->group('UserID')->select();
                }

                $data = $this->changeKeys($data, 'userid');

            }elseif ($key=='R'){

                $map['UserId2'] = array('in',$vips);

                if($type=='player'){
                    $map['UserID'] = array('not in',$vips);
                }elseif ($type=='vip'){
                    $map['UserID'] = array('in',$vips);
                }

                if($countType=='times'){
                    $data = $m1->where($map)->where('UserID!=UserId2')->field('UserId2,count(UserId2) as total')->group('UserId2')->select();
                }elseif ($countType=='person'){
                    $data = $m1->where($map)->where('UserID!=UserId2')->field('UserId2,count(distinct UserID) as total')->group('UserId2')->select();
                }else{
                    $data = $m1->where($map)->where('UserID!=UserId2')->field('UserId2,sum(Score) as total')->group('UserId2')->select();
                }

                $data = $this->changeKeys($data, 'userid2');
            }

        return $data;
    }

}