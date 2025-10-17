<?php
//代理报表

namespace App\Services\Report\Report;

use App\Libs\Helper;
use App\Models\Agent;
use App\Models\DataDailyAgentBusiness;
use App\Models\DataDailyRoomUser;
use App\Models\DataDailyRoomUserGtype;
use App\Models\Member;
use App\Models\MoneyLog;
use Illuminate\Support\Facades\DB;

class AgentReport {
    public function operationState($agent_id, $begin_date, $end_date) {
        //新玩家
        $newSubAgentCount = Agent::where([
            ['created_at', '>=', $begin_date], ['created_at', '<=', $end_date], ['parent_agent_id', '=', $agent_id]
        ])->count();
        $newMember = Member::where([
            ['created_at', '>=', $begin_date], ['created_at', '<=', $end_date], ['agent_id', '=', $agent_id]
        ])->get(['member_id', 'agent_id', 'login_name', 'created_at']);
        $newMemberCount = $newMember->count();
        $newMemberIdArr = $newMember->pluck('member_id')->toArray();
        $newMemberValid = DataDailyRoomUser::where([['created_at', '>=', $begin_date], ['created_at', '<=', $end_date]])
            ->whereIn('login_name', $newMember->pluck('login_name')->toArray())->count();

        $newMemberDeposit = MoneyLog::where([
            ['created_at', '>=', $begin_date], ['created_at', '<=', $end_date], ['money_operate_type', '=', 'deposit']
        ])->whereIn('member_agent_id', $newMemberIdArr)->get(['money_change', 'member_agent_id']);
        $newMemberDepositCount = $newMemberDeposit->unique('member_agent_id')->count();
        $newMemberDepositTotal = $newMemberDeposit->sum('money_change');
        $newMemberDrawTotal = MoneyLog::where([
            ['created_at', '>=', $begin_date], ['created_at', '<=', $end_date],
            ['money_operate_type', '=', 'money_draw']
        ])
            ->whereIn('member_agent_id', $newMemberIdArr)->sum('money_change');

        //总数据
        $agentMember = Member::where('agent_id', $agent_id)->get(['member_id', 'agent_id', 'login_name']);
        $agentMemberIdArr = $agentMember->pluck('member_id')->toArray();
        $depositTotal = MoneyLog::whereIn('member_agent_id', $agentMemberIdArr)
            ->where([
                ['money_operate_type', '=', 'deposit'], ['created_at', '>=', $begin_date],
                ['created_at', '<=', $end_date]
            ])->sum('money_change');
        $drawTotal = MoneyLog::whereIn('member_agent_id', $agentMemberIdArr)
            ->where([
                ['money_operate_type', '=', 'money_draw'], ['created_at', '>=', $begin_date],
                ['created_at', '<=', $end_date]
            ])->sum('money_change');

        $agentMemberName = $agentMember->pluck('login_name')->toArray();
        $dataUser = DataDailyRoomUser::where([['created_at', '>=', $begin_date], ['created_at', '<=', $end_date]])
            ->whereIn('login_name', $agentMemberName)
            ->get(['data_daily_room_user_id', 'login_name', 'win_lose', 'created_at']);
        $memberValid = $dataUser->unique('login_name')->count();
        $memberWinLose = $dataUser->sum('win_lose');

        $data = [
            'newSubAgentCount'      => $newSubAgentCount,
            'newMemberCount'        => $newMemberCount,
            'newMemberValid'        => $newMemberValid,
            'newMemberDepositCount' => $newMemberDepositCount,
            'newMemberDepositTotal' => number_format($newMemberDepositTotal, 2),
            'newMemberDrawTotal'    => number_format($newMemberDrawTotal, 2),
            'depositTotal'          => number_format($depositTotal, 2),
            'drawTotal'             => number_format($drawTotal, 2),
            'memberValid'           => $memberValid,
            'memberWinLose'         => number_format($memberWinLose, 2)
        ];
        return $data;
    }

    public function businessState($cond) {
        $agent_id   = $cond['agent_id'];
        $begin_date = $cond['begin_date'];
        $end_date   = $cond['end_date'];
        $sub_agent_ids    = Agent::where('parent_agent_id', $agent_id)->pluck('agent_id')->toArray();
        $sub_member_names = Member::whereIn('agent_id', $sub_agent_ids)->pluck('login_name')->toArray();
        $direct_members   = Member::where('agent_id', $agent_id)->get();
        $direct_member_data = DataDailyRoomUserGtype::where([
            ['calc_date', '>=', $begin_date],
            ['calc_date', '<=', $end_date],
        ])->whereIn('login_name', $direct_members->pluck('login_name')->toArray())
            ->where(function ($query) use ($cond) {
                if (!empty($cond['login_name'])) {
                    $query->where('login_name', $cond['login_name']);
                }
            })
            ->select(\DB::raw('data_daily_room_user_gtype_id,login_name,sum(bet_count) as bet_count, sum(money_total) as money_total,sum(money_use) as money_use, sum(win_lose) as win_lose'))
            ->groupBy('login_name')
            ->paginate(Helper::getSetting('PAGE_COUNT', 'company_super'));

        /*$direct_member_data = \DB::table('tb_data_daily_room_user_gtype')->where([
            ['calc_date','>=',$begin_date],
            ['calc_date','<=',$end_date],
        ])->whereIn('login_name',$direct_members->pluck('login_name')->toArray())
            ->where(function($query) use ($cond){
                if(!empty($cond['login_name'])){
                    $query->where('login_name',$cond['login_name']);
                }
            })->select(\DB::raw('data_daily_room_user_gtype_id,login_name,sum(bet_count) as bet_count, sum(money_total) as money_total,sum(money_use) as money_use, sum(win_lose) as win_lose'))
            ->groupBy('login_name')
            ->paginate(Helper::getSetting('PAGE_COUNT','company_super'));*/

        $sub_member_data = DataDailyRoomUserGtype::where([
            ['calc_date', '>=', $begin_date],
            ['calc_date', '<=', $end_date],
        ])->whereIn('login_name', $sub_member_names)
            ->where(function ($query) use ($cond) {

            })->groupBy(['login_name'])->get([
                'data_daily_room_user_gtype_id', 'login_name', 'bet_count', 'money_total', 'money_use', 'win_lose'
            ]);
        $sub_statistical = [
            'agent_count'      => count($sub_agent_ids),
            'net_member_count' => $sub_member_data->unique('login_name')->count(),
            'bet_count'        => $sub_member_data->sum('bet_count'),
            'money_total'      => number_format($sub_member_data->sum('money_total'), 2),
            'money_use'        => number_format($sub_member_data->sum('money_use'), 2),
            'win_lose'         => number_format($sub_member_data->sum('win_lose'), 2)
        ];

        return ['direct_member_data' => $direct_member_data, 'sub_statistical' => $sub_statistical];
    }

    public function agentSubBusiness($cond) {
        $agent_id = $cond['agent_id'];
        $begin_date = $cond['begin_date'];
        $end_date = $cond['end_date'];
        $sub_member_names = Member::where('agent_id', $agent_id)->pluck('login_name')->toArray();

        $sub_member_data = DataDailyRoomUserGtype::where([
            ['calc_date', '>=', $begin_date],
            ['calc_date', '<=', $end_date],
        ])->whereIn('login_name', $sub_member_names)
            ->where(function ($query) use ($cond) {

            })->groupBy(['login_name'])->get([
                'data_daily_room_user_gtype_id', 'login_name', 'bet_count', 'money_total', 'money_use', 'win_lose'
            ]);

        $sub_statistical = [
            'net_member_count' => $sub_member_data->unique('login_name')->count(),
            'bet_count'        => $sub_member_data->sum('bet_count'),
            'money_total'      => number_format($sub_member_data->sum('money_total'), 2),
            'money_use'        => number_format($sub_member_data->sum('money_use'), 2),
            'win_lose'         => number_format($sub_member_data->sum('win_lose'), 2)
        ];
        return $sub_statistical;
    }

    public function createAgentBusinessReport($date = null) {
        $date = $date ?: date('Y-m-d', strtotime('-1 day'));
        //判断是否已存在记录
        $is_created = DataDailyAgentBusiness::where('date', $date)->first();
        if ($is_created) {
            return ['status' => FAILED, 'msg' => '已存在该日记录！'];
        }
        DB::beginTransaction();
        $allAgents = Agent::where('is_active', 1)/*->whereColumn('company_id','!=','agent_id')*/
        ->get();
        foreach ($allAgents as $agent) {
            $subAgentIds = $this->_subAgentIds($agent->agent_id, $allAgents);
            $subAndSelfIds = array_prepend($subAgentIds, $agent->agent_id);
            $members = Member::whereIn('agent_id', $subAndSelfIds)->get();
            $memberDataGame = DataDailyRoomUserGtype::where('calc_date', $date)
                ->whereIn('login_name', $members->pluck('login_name')->toArray())
                ->get();
            $moneyMovement = MoneyLog::whereIn('member_agent_id', $members->pluck('member_id')->toArray())
                ->whereDate('created_at', $date)
                ->whereIn('money_operate_type',
                    ['deposit', 'money_draw', 'money_draw_admin', 'gift_money', 'backwater_settle'])
                ->get();
            //新注册数
            $member_new = $members->filter(function ($member, $key) use ($date) {
                return (($member->created_at >= $date . ' 00:00:00') and ($member->created_at <= $date . ' 23:59:59'));
            })->count();
            //新开户数
            $member_first_deposit = $members->where('agent_id', $agent->agent_id)
                ->filter(function ($member, $key) use ($date) {
                    return (($member->first_deposit_time >= $date . ' 00:00:00') and
                        ($member->first_deposit_time <= $date . ' 23:59:59'));
                })->count();
            //活跃人数
            $member_active = $memberDataGame->unique('login_name')->count();
            //投注金额
            $money_total = $memberDataGame->sum('money_total');
            //有效金额
            $money_use = $memberDataGame->sum('money_use');
            //输赢
            $win_lose = $memberDataGame->sum('win_lose');
            //交上家    待定
            $delivery = 0;   //待定
            //返水
            $backwater = $moneyMovement->where('money_operate_type', 'backwater_settle')->sum('money_change');
            //存款
            $deposit = $moneyMovement->where('money_operate_type', 'deposit')->sum('money_change');
            //取款
            $withdrawal = $moneyMovement->whereIn('money_operate_type', ['money_draw', 'money_draw_admin'])
                ->sum('money_change');
            //手续费
            $service_charge = 0;  //待定
            $data = [
                'company_id'           => $agent->company_id,
                'agent_id'             => $agent->agent_id,
                'date'                 => $date,
                'member_new'           => $member_new,
                'member_first_deposit' => $member_first_deposit,
                'member_active'        => $member_active,
                'money_total'          => $money_total,
                'money_use'            => $money_use,
                'win_lose'             => $win_lose,
                'delivery'             => $delivery,
                'backwater'            => $backwater,
                'deposit'              => $deposit,
                'withdrawal'           => $withdrawal,
                'service_charge'       => $service_charge
            ];
            $ret = DataDailyAgentBusiness::create($data);
            if (!$ret) {
                DB::rollBack();
                return ['status' => FAILED, 'msg' => '系统繁忙，请稍后再试！'];
            }
        }
        DB::commit();
        return ['status' => SUCCESS, 'msg' => '添加成功！'];
    }

    private function _subAgentIds($agent_id, $agents) {
        $_subordinateIds = [];
        $child_ids = $this->_agentSubordinateIds($agents, $agent_id, $_subordinateIds);
        unset($_subordinateIds);
        return $child_ids;
    }

    private function _agentSubordinateIds($agents, $agent_id, &$_subordinateIds) {
        foreach ($agents as $item) {
            if ($item->parent_agent_id == $agent_id) {
                $_subordinateIds[] = $item->agent_id;
                $this->_agentSubordinateIds($agents, $item->agent_id, $_subordinateIds);
            }
        }
        return $_subordinateIds;
    }

}
