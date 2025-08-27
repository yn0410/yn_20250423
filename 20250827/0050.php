<?php

/**
 * 0050 (Yuanta Taiwan Top 50 ETF, Yahoo: 0050.TW)
 * 近 30 年：每年最後一個交易日 Close / Adj Close、年報酬率、年化報酬率 (CAGR)
 * - 先用 Yahoo (帶 Cookie)；若失敗改用 TWSE 官方月資料 (STOCK_DAY)
 * - close/adj_close 四捨五入到小數點 2 位
 * - return 與 CAGR 以百分比字串到小數點 2 位
 * - 同時輸出 JSON 與 CSV
 */

date_default_timezone_set('Asia/Taipei');

// 目標：0050
$tickerYahoo = '0050.TW';
$stockNo     = '0050';

// Yahoo 下載區間（抓早一點沒關係）
$period1 = strtotime('2000-01-01');
$period2 = time();

$yahooHistoryPage = 'https://finance.yahoo.com/quote/' . $tickerYahoo . '/history?p=' . $tickerYahoo;
$yahooDownloadUrl = sprintf(
    'https://query1.finance.yahoo.com/v7/finance/download/%s?period1=%d&period2=%d&interval=1d&events=history&includeAdjustedClose=true',
    urlencode($tickerYahoo),
    $period1,
    $period2
);

/** 以 Cookie 方式抓取（Yahoo 用） */
function curl_get_with_cookies($url, $cookieFile, $referer = null)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HEADER         => false,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_HTTPHEADER     => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
            'Accept: text/csv,application/csv,text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ],
    ]);
    if ($referer) curl_setopt($ch, CURLOPT_REFERER, $referer);
    $data = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return [$http, $data, $err];
}

/** Yahoo：先開歷史頁取 Cookie，再下載 CSV */
function fetch_yahoo_csv($historyPage, $downloadUrl)
{
    $cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'yahoo_cookie_' . uniqid() . '.txt';
    [$http1, $html, $err1] = curl_get_with_cookies($historyPage, $cookieFile);
    [$http2, $csv,  $err2] = curl_get_with_cookies($downloadUrl, $cookieFile, $historyPage);
    @unlink($cookieFile);
    return [$http2, $csv, $err2];
}

/** 取得 TWSE 月資料：STOCK_DAY JSON（會傳回某個月份的每日 OHLC） */
function fetch_twse_month_json($yyyymmdd, $stockNo)
{
    // 舊路徑仍可用：/exchangeReport/STOCK_DAY?response=json&date=YYYYMMDD&stockNo=0050
    $url = sprintf(
        'https://www.twse.com.tw/exchangeReport/STOCK_DAY?response=json&date=%s&stockNo=%s',
        $yyyymmdd,
        $stockNo
    );
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126 Safari/537.36',
            'Accept: application/json,text/plain,*/*',
            'Referer: https://www.twse.com.tw/zh/trading/historical/stock-day.html'
        ],
    ]);
    $data = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($http !== 200 || $data === false) return [null, $http, $err];
    $json = json_decode($data, true);
    if (!is_array($json)) return [null, $http, 'json decode failed'];
    return [$json, $http, null];
}

/** 將 TWSE 日期格式（可能是 民國年 或 2024/01/02）轉成 YYYY-MM-DD */
function normalize_twse_date($s)
{
    $s = trim($s);
    // 可能為 "113/01/03"（民國年）
    if (preg_match('#^(\d{2,3})/(\d{2})/(\d{2})$#', $s, $m)) {
        $y = (int)$m[1];
        $mth = $m[2];
        $d = $m[3];
        // 假設 <1911 為民國年
        $year = ($y < 1911) ? $y + 1911 : $y;
        return sprintf('%04d-%02d-%02d', $year, $mth, $d);
    }
    // 也可能已是 "2024-01-03" 或 "2024/01/03"
    $s = str_replace('/', '-', $s);
    if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $s)) return $s;
    // fallback
    $ts = strtotime($s);
    return $ts ? date('Y-m-d', $ts) : null;
}

/** 將帶千分位或特殊符號的數字字串轉 float */
function to_float_or_null($s)
{
    $s = trim($s);
    if ($s === '' || $s === '-' || strtolower($s) === 'null') return null;
    $s = str_replace([',', ' '], '', $s);
    // 有時會出現 "X" 代表無價
    if (!is_numeric($s)) return null;
    return (float)$s;
}

/** 以 Yahoo 或 TWSE 組出「每年最後一個交易日」Map */
function build_last_per_year_from_yahoo_csv($csv)
{
    $fp = fopen('php://memory', 'r+');
    fwrite($fp, $csv);
    rewind($fp);
    $header = fgetcsv($fp);
    $idx = array_flip($header);
    foreach (['Date', 'Close', 'Adj Close'] as $col) {
        if (!isset($idx[$col])) return null;
    }
    $lastPerYear = [];
    while (($row = fgetcsv($fp)) !== false) {
        if (count($row) < count($header)) continue;
        $rec = array_combine($header, $row);
        if (empty($rec['Date']) || $rec['Close'] === 'null') continue;
        $date = $rec['Date'];
        $ts   = strtotime($date);
        if ($ts === false) continue;
        $year = (int)date('Y', $ts);
        if (!isset($lastPerYear[$year]) || $ts > strtotime($lastPerYear[$year]['Date'])) {
            $lastPerYear[$year] = $rec; // Date / Close / Adj Close
        }
    }
    fclose($fp);
    return $lastPerYear;
}

function build_last_per_year_from_twse($stockNo, $fromY = '2003', $fromM = '01')
{
    $lastPerYear = [];
    // 由 2003-01 開始，一直到下個月的第一天（確保涵蓋本月）
    $start = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', (int)$fromY, (int)$fromM));
    $end   = new DateTime('first day of next month');

    for ($d = clone $start; $d < $end; $d->modify('+1 month')) {
        $dateParam = $d->format('Ymd'); // YYYYMMDD
        list($json, $http, $err) = fetch_twse_month_json($dateParam, $stockNo);
        if (!$json || !isset($json['data']) || !is_array($json['data'])) continue;

        foreach ($json['data'] as $row) {
            // 典型欄位順序：日期, 成交股數, 成交金額, 開盤價, 最高價, 最低價, 收盤價, 漲跌價差, 成交筆數
            if (count($row) < 7) continue;
            $dateStr = normalize_twse_date($row[0]);
            $close   = to_float_or_null($row[6]);
            if (!$dateStr || $close === null) continue;

            $ts   = strtotime($dateStr);
            $year = (int)date('Y', $ts);

            // TWSE 無「還原價」，Adj Close 以 null 表示
            if (!isset($lastPerYear[$year]) || $ts > strtotime($lastPerYear[$year]['Date'])) {
                $lastPerYear[$year] = [
                    'Date'      => $dateStr,
                    'Close'     => $close,
                    'Adj Close' => null
                ];
            }
        }
    }
    return $lastPerYear;
}

// ===== 1) 嘗試 Yahoo =====
[$yHttp, $yData, $yErr] = fetch_yahoo_csv($yahooHistoryPage, $yahooDownloadUrl);

$useTWSE = ($yHttp !== 200 || $yData === false || stripos($yData, 'Date,Open,High,Low,Close') !== 0);
$lastPerYear = [];

if ($useTWSE) {
    // ===== 2) Yahoo 失敗 → 用 TWSE 月資料拼每日，再取每年最後交易日 =====
    $lastPerYear = build_last_per_year_from_twse($stockNo, '2003', '01'); // 0050 2003/06 上市，抓早一點OK
    if (!$lastPerYear) {
        echo "下載失敗 (Yahoo HTTP $yHttp / TWSE 無資料)\n";
        exit(1);
    }
    $source = 'twse';
} else {
    $tmp = build_last_per_year_from_yahoo_csv($yData);
    if (!$tmp) {
        // Yahoo CSV 結構不符，也改用 TWSE
        $lastPerYear = build_last_per_year_from_twse($stockNo, '2003', '01');
        if (!$lastPerYear) {
            echo "下載失敗 (Yahoo CSV 結構異常 / TWSE 無資料)\n";
            exit(1);
        }
        $source = 'twse';
    } else {
        $lastPerYear = $tmp;
        $source = 'yahoo';
    }
}

// 只取最近 30 年（0050 會少於 30 無妨）
$years = array_keys($lastPerYear);
sort($years);
$years = array_slice($years, -30);

// 組結果
$result = [];
foreach ($years as $y) {
    $r = $lastPerYear[$y];
    $close    = isset($r['Close']) ? (float)$r['Close'] : null;
    $adjClose = isset($r['Adj Close']) && $r['Adj Close'] !== '' ? (float)$r['Adj Close'] : null;

    $result[] = [
        'year'       => $y,
        'date'       => $r['Date'] ?? '',
        'close'      => is_numeric($close) ? round($close, 2) : null,
        'adj_close'  => is_numeric($adjClose) ? round($adjClose, 2) : null,
    ];
}

// 每年報酬率
for ($i = 0; $i < count($result); $i++) {
    if ($i === 0) {
        $result[$i]['return_close'] = null;
        $result[$i]['return_adj_close'] = null;
        continue;
    }
    $prevClose = $result[$i - 1]['close'];
    $prevAdj   = $result[$i - 1]['adj_close'];
    $curClose  = $result[$i]['close'];
    $curAdj    = $result[$i]['adj_close'];

    $rClose = (is_numeric($prevClose) && $prevClose != 0 && is_numeric($curClose)) ? ($curClose - $prevClose) / $prevClose * 100 : null;
    $rAdj   = (is_numeric($prevAdj)   && $prevAdj   != 0 && is_numeric($curAdj))   ? ($curAdj   - $prevAdj)   / $prevAdj   * 100 : null;

    $result[$i]['return_close']     = is_null($rClose) ? null : round($rClose, 2) . "%";
    $result[$i]['return_adj_close'] = is_null($rAdj)   ? null : round($rAdj, 2) . "%";
}

// CLI 表格輸出
echo ($source === 'twse' ? "[使用 TWSE 月資料組成：Adj Close 為 null]\n" : "");
echo "0050 近 30 年：每年最後交易日收盤價與報酬率（Close / Adj Close）\n";
echo str_pad("Year", 6) .
    str_pad("Date", 14) .
    str_pad("Close", 12) .
    str_pad("AdjClose", 12) .
    str_pad("R_Close", 12) .
    str_pad("R_Adj", 12) . "\n";
echo str_repeat("-", 68) . "\n";
foreach ($result as $row) {
    echo str_pad($row['year'], 6) .
        str_pad($row['date'], 14) .
        str_pad(isset($row['close']) && $row['close'] !== null ? number_format($row['close'], 2, '.', '') : 'null', 12) .
        str_pad(isset($row['adj_close']) && $row['adj_close'] !== null ? number_format($row['adj_close'], 2, '.', '') : 'null', 12) .
        str_pad($row['return_close'] ?? 'null', 12) .
        str_pad($row['return_adj_close'] ?? 'null', 12) . "\n";
}

// CAGR（用第一年與最後一年；資料不足則 null）
$N = count($result) - 1;
if ($N > 0) {
    $firstClose = $result[0]['close'];
    $lastClose  = $result[$N]['close'];
    $firstAdj   = $result[0]['adj_close'];
    $lastAdj    = $result[$N]['adj_close'];

    $cagrClose = (is_numeric($firstClose) && is_numeric($lastClose) && $firstClose > 0)
        ? pow($lastClose / $firstClose, 1 / $N) - 1 : null;
    $cagrAdj   = (is_numeric($firstAdj) && is_numeric($lastAdj) && $firstAdj > 0)
        ? pow($lastAdj / $firstAdj, 1 / $N) - 1 : null;
} else {
    $cagrClose = null;
    $cagrAdj = null;
}

echo "\n=== 年化報酬率 (CAGR) ===\n";
echo "Close CAGR     : " . (is_null($cagrClose) ? 'null' : round($cagrClose * 100, 2) . "%") . "\n";
echo "Adj Close CAGR : " . (is_null($cagrAdj) ? 'null' : round($cagrAdj * 100, 2) . "%") . "\n";

// JSON 輸出
$jsonPath = __DIR__ . '/0050_last_trading_day_30y.json';
$output = [
    'data' => $result,
    'cagr' => [
        'close' => is_null($cagrClose) ? null : round($cagrClose * 100, 2) . "%",
        'adj_close' => is_null($cagrAdj) ? null : round($cagrAdj * 100, 2) . "%"
    ],
    'source' => $source,
    'ticker' => $tickerYahoo
];
file_put_contents($jsonPath, json_encode($output, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "\nJSON 已輸出：$jsonPath\n";

// CSV 輸出
$csvPath = __DIR__ . '/0050_last_trading_day_30y.csv';
$fp = fopen($csvPath, 'w');
fputcsv($fp, ['year', 'date', 'close', 'adj_close', 'return_close', 'return_adj_close']);
foreach ($result as $row) {
    fputcsv($fp, [
        $row['year'],
        $row['date'],
        isset($row['close']) && $row['close'] !== null ? number_format($row['close'], 2, '.', '') : null,
        isset($row['adj_close']) && $row['adj_close'] !== null ? number_format($row['adj_close'], 2, '.', '') : null,
        $row['return_close'],
        $row['return_adj_close']
    ]);
}
fputcsv($fp, []); // 空行
fputcsv($fp, [
    'CAGR',
    '',
    '',
    '',
    is_null($cagrClose) ? null : round($cagrClose * 100, 2) . "%",
    is_null($cagrAdj) ? null : round($cagrAdj * 100, 2) . "%"
]);
fclose($fp);
echo "CSV 已輸出：$csvPath\n";
