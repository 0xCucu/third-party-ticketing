<?php

namespace Muskid\DataBuilder;

use App\Models\Performances;
use App\Models\PerformancesStation;
use Illuminate\Database\Eloquent\Model;

class KuGouDataBuilder extends BasicDataBuilder implements DataBuilderInterface
{

    public function builder(Model $model)
    {
        $performances = optional($model->performances);
        $liveHouse = optional($model->liveHouse);
        $base_url = config('app.base_url');
        if (!isset($model->performances)) {
            throw new \Exception('推送酷狗票务失败 没有找到 场次【' . $model->id . '】对应演出信息或 演出已经结束不进行推送');
            return false;
        }
        if (!isset($model->ticket)) {
            throw new \Exception('推送酷狗票务失败没有找到 场次【' . $model->id . '】对应票品或 演出已经结束不进行推送');
            return false;

        }
        if ($model->ticket->isEmpty()) {
            throw new \Exception('推送酷狗票务失败 没有找到 场次【' . $model->id . '】对应票品或 演出已经结束不进行推送');
            return false;

        }
        return array_urlencode([
            'sourceid' => $model->id,
            'item_name' => mb_substr(html_entity_decode($performances->title), 0, 70, 'utf-8'),
            'cover' => imgUrl(trim($performances->main_picture_url, "/")),
            'province' => $model->province,
            'city' => $model->city,
            'ven_name' => $liveHouse->title,
            'venue_address' => $liveHouse->address,
            'type' => 1,
            'show_time' => strtotime($model->start_date),
            'end_time' => $model->end_date,
            'performer' => $model->performers,
            'status' => $this->status($model),
            'prices' => json_encode(array_urlencode($this->prices($model->ticket)), JSON_UNESCAPED_UNICODE),
            'ticket_url' => $base_url . 'new/station/' . $model->id.'?from=kugou',
            'detail_url' => $base_url . 'new/station/' . $model->id.'?from=kugou',
        ], 'prices');
    }

    public function prices($data)
    {
        $res = [];
        foreach ($data as $ticket) {
            $ticketStatus = 1;
            if ($ticket->sell_total > 0 && $ticket->sell_total <= $ticket->selled_total) {
                $ticketStatus = 0; // 0代表已售罄
            }
            //只推送大于0的票品
            if ($ticket->presell_price > 0) {
                $res[] = [
                    'status' => $ticketStatus,
                    'price' => $ticket->presell_price,
                    'desc' => $ticket->remark,
                ];
            }

        }
        return $res;
    }

    public function status($model)
    {
        switch ($model->status) {
            case 0:
                return 1; //状态为0准备中时 返回项目待定
                break;
            case 2:
                return 1;
                break;
            case 3:
                return 3;
                break;
            case 4:
                return 3;
                break;
            default:
                return 2;
                break;
        }
    }

    public function build($p_id, $ps_id = '')
    {

        $res = PerformancesStation::has('performances')->with(
            [
                'performances' => function ($query) use ($p_id) {
                    $query->whereId($p_id);
                },
                'liveHouse',
                'ticket',
            ]
        )->effectivePerformancesStation()
            ->whereId($ps_id)
            ->get()
            ->reject(function ($item) {
                return empty($item['performances']);
            });
        if ($res->isEmpty()) {
            throw new \Exception('推送酷狗票务失败 没有找到 场次【' . $ps_id . '】对应演出信息 或 演出已经结束不进行推送');
        }
        return $res;
    }
}
