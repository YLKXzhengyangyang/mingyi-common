<?php
/**
 * Created by PhpStorm.
 * User: diamonds.a
 * Date: 2017/6/8
 * Time: 下午3:16
 */

namespace Mingyi\Common;


class Helper
{

//根据时间加密token
    public function getToken($token = '', $str = '', $to_sup = true)
    {
        if (!$token) {
            $token = strtoupper(env('ACCESS_KEY', 'MYNAMEDIAMONDS@A!KILL^&!MING@YIQ'));
        }
        $str = $str ? $str : 'MINGYI';
        $l = strlen($str);
        $l = $l > 8 ? 8 : $l;
        $len = 0 - $l;
        $time = substr(date('Ymd'), $len);
        $b = $time;
        while ($time !== '') {
            $char = $time[0];
            $token = substr_replace($token, $str[0], $char, 0);
            $time = substr_replace($time, '', 0, 1);
            $str = substr_replace($str, '', 0, 1);
        }
        if (strlen($l) < 2) {
            $l = 'A' . $l;
        }
        return $to_sup ? strtoupper($l . $token . $b) : $l . $token . $b;
    }

//解密获取原生token
    public function getProToken($token = '')
    {
        if (!$token) {
            return $token;
        }
        $l = substr($token, 0, 2);
        if (intval($l) < 1) {
            $l = substr($l, -1);
        }
        $len = 0 - $l;
        if ($len >= 0) {
            return $token;
        }
        $token = substr($token, 2);
        $time = substr($token, $len);
        $now = date('Ymd');
        $pre = substr_replace($now, $time, $len, $l);
        $str = [];
        while ($time !== '') {
            $index = strlen($time) - 1;
            $s = $time[$index];
            array_unshift($str, $token[$s]);
            $token = substr_replace($token, '', $s, 1);
            $time = substr_replace($time, '', $index, 1);
        }
        $token = substr_replace($token, '', $len);
        return ['token' => $token, 'time' => $pre, 'str' => join('', $str)];
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign($array)
    {
        $key = env('ACCESS_KEY', 'MYNAMEDIAMONDS@A!KILL^&!MING@YIQ');
        //签名步骤一：按字典序排序参数
        ksort($array);
        $string = $this->ToUrlParams($array);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    protected function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if ($k != "sign") {
                if (is_array($v)) {
                    $buff .= $k . "=" . json_encode($v) . "&";
                } else {
                    $buff .= $k . "=" . $v . "&";
                }

            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 单位转换
     * @param $data [单位信息]
     * @param $fromUnitId [待转为的单位ID]
     * @param $toUnitId [要转换成的单位ID]
     * @param int $num [数量]
     * @return array
     */
    public function doFactor($data, $fromUnitId, $toUnitId, $num = 1)
    {
        $units = [];
        $flipUnits = [];
        $proFactor = [];
        $pro_unit =[];
        foreach ($data as $value) {
            $units[$value['id']] = $value['factor'];
            $flipUnits[$value['factor']] = $value;
            $proFactor[] = $value['factor'];
            $pro_unit[$value['id']]=$value;
        }
        if (!isset($units[$fromUnitId]) || !isset($units[$toUnitId])) {
            return [];
        }
        if($fromUnitId == $toUnitId){
            return [[
                'sort' => 1,
                'unit' => $pro_unit[$fromUnitId]['name'],
                'unit_id' => $fromUnitId,
                'num' => $num
            ]];
        }
        sort($proFactor, SORT_NUMERIC);
        $amount = $units[$fromUnitId] * $num;
        $u = [];
        $expNum = $this->getLastUnit($proFactor, $amount);
        $i = 1;
        while ($amount) {
            $n = floor($amount / $expNum);
            $temp = [
                'sort' => $i,
                'unit' => $flipUnits[$expNum]['name'],
                'unit_id' => $flipUnits[$expNum]['id'],
                'num' => $n
            ];
            if ($n) {
                $u[] = $temp;
            }

            if ($amount < $proFactor[1]) {
                break;
            }
            $amount %= $expNum;
            $i++;
            $expNum = $this->getLastUnit($proFactor, $amount);
        }
        return $u;
    }


    private function getLastUnit($units, $num)
    {
        $filter = array_filter($units, function ($val) use ($num) {
            return $val <= $num;
        });
        sort($filter, SORT_NUMERIC);
        return array_pop($filter);
    }

    /**
     * 请求头信息
     * @param array $data
     * @return array
     */
    public function head($data = [])
    {
        return ['headers' => ['token' => $this->getToken($this->MakeSign($data))]];
    }

    /**
     * 查询参数
     * @param array $data
     * @return array
     */
    public function q($data = [])
    {
        $search = [];
        foreach ($data as $key => $val) {
            $search[$key] = is_array($val) ? json_encode($val, true) : $val;
        }
        return array_merge(['query' => $search], $this->head($search));
    }

    /**
     * body 参数
     * @param array $data
     * @return array
     */
    public function p($data = [])
    {
        $search = [];
        foreach ($data as $key => $val) {
            $search[$key] = is_array($val) ? json_encode($val, true) : $val;
        }
        return array_merge(['form_params' => $search], $this->head($search));
    }
}