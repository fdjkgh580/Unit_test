<?php 
namespace Jsnlib\Codeigniter;

class Unit_test 
{
    static protected $ci;

    static protected function style()
    {
        return 
        '
            <style>
                @import url(//fonts.googleapis.com/earlyaccess/notosanstc.css);
                .conttainer-unit-test > *{
                    font-family: "Noto Sans TC", sans-serif;
                }
                table.unit-test {
                    border: 1px solid #A1A1A1;
                    border-collapse: collapse;
                    width: 70%;
                    min-width: 768px;
                    margin: 0 auto;
                    margin-bottom: 1em;
                }
                table.unit-test tbody .item,
                table.unit-test tbody .result {
                    padding: 0.2em 1em;
                    font-size: 14px;
                    line-height: 2em;
                }
                table.unit-test tbody .item {
                    width: 10%;
                    min-width: 150px;
                    text-align: right;
                    font-weight: 800;
                    background: #333333;
                    color: #c3c3c3;
                }
                table.unit-test tbody .result {
                    text-align: left;
                    font-weight: 400;
                    color: #4e4e4e;
                }
                table.unit-test tbody tr.tr-passed .result {
                    background: rgba(54, 216, 111, 0.51);
                    color: white;
                }
                table.unit-test tbody tr.tr-failed .result {
                    background: #dc404f;
                    color: white;
                }

                .num-result {
                    position: fixed;
                    width: 200px;
                    background: #fddec9;
                    top: 0px;
                    left: 0px;
                    right: 0px;
                    line-height: 41px;
                    padding: 1em;
                    height: 100%;
                    font-size: 20px;
                }

                .failed {
                    color: #dc404f;
                    font-weight: 600;
                }

                .passed {
                    color: rgba(54, 216, 111, 0.51);
                    font-weight: 600;
                }

            </style>
        ';
    }

    
    // 組合 <tr> 的 class 名稱
    static public  function listclass($item)
    {
        $str = str_replace(" ", "-", $item);
        $str = strtolower($str);
        return "tr-{$str}";
    }

    // 用 <tr> 包圍
    static public function wrap_tr($item, $result, $callback)
    {
        if ($item == "Result")
        {
            // 依照通過不通過給予 tr 的 class
            if ($result == "Passed") 
            {
                $tag_before = '<tr class="'. self::listclass($item) . ' tr-passed">';
            }

            elseif ($result == "Failed")
            {
                $tag_before = '<tr class="'. self::listclass($item) . ' tr-failed">';
            }
        }
        else 
            $tag_before = '<tr class="'. self::listclass($item) . '">';

        $tag_after .= "</tr>";

        // 利用 callback 組中 <tr> 底下的內容
        $data = new \Jsnlib\Ao(['item' => $item, 'result' => $result]);
        $content .= $callback($data);

        return $tag_before . $content . $tag_after;
    }

    // 計算結果數量
    static protected function count_result($datalist):\Jsnlib\Ao
    {
        $box = new \Jsnlib\Ao(
        [
            'passed' => 0,
            'failed' => 0,
        ]);

        foreach ($datalist as $datainfo)
        {
            if ($datainfo->get('Result') == "Passed") $box->passed += 1;
            elseif ($datainfo->get("Result") == "Failed") $box->failed += 1;
        }

        return $box;
    }

    static protected function num(\Jsnlib\Ao $box)
    {
        return 
        '
            <div class="num-result">
                Passed: <span class="passed">' . $box->passed . '</span>
                <br>
                Failed: <span class="failed">' . $box->failed . '</span>
            </div>
        ';
    }

    // 匯出報表
    static public function report():string 
    {
        self::$ci =& get_instance();

        $result = self::$ci->unit->result();
        $datalist = new \Jsnlib\Ao($result);

        // 通過 | 不通過數量
        $box = self::count_result($datalist);
        $numblock = self::num($box);

        $style = self::style();
        $str = null;

        if (count($datalist) > 0) 
        {
            foreach ($datalist as $datainfo)
            {
                $str .= '<table class="unit-test">';
                foreach ($datainfo as $item => $result)
                {
                    $str .= self::wrap_tr($item, $result, function ($data)
                    {
                        // echo $data;
                        $inner = '<th class="item">' . $data->item . '</th>';
                        $inner .= '<td class="result">' . $data->result . '</td>';
                        return $inner;
                    });
                }
                $str .= '</table>';
            }
           
        }

        $content = $style . '<div class="conttainer-unit-test">' . $numblock . $str . '</div>';
        
        return $content;
    }
}