<?php

/**
 * VT 近 30 年：每年最後一個交易日 Close / Adj Close、年報酬率、年化報酬率 (CAGR)
 * - 先用 Yahoo (帶 Cookie)；若 401/失敗 自動改用 Stooq (無 Adj Close -> 設為 null)
 * - close/adj_close 四捨五入到小數點 2 位
 * - return 與 CAGR 以百分比字串到小數點 2 位
 * - 同時輸出 JSON 與 CSV
 */

date_default_timezone_set('Asia/Taipei');

$ticker  = 'VT';
$period1 = strtotime('2005-01-01');  // VT 2008 上市，抓早一點 OK
$period2 = time();

$yahooHistoryPage = 'https://finance.yahoo.com/quote/VT/history?p=VT';
$yahooDownloadUrl = sprintf(
    'https://query1.finance.yahoo.com/v7/finance/download/%s?period1=%d&period2=%d&interval=1d&events=history&includeAdjustedClose=true',
    urlencode($ticker),
    $period1,
    $period2
);
$stooqUrl = 'https://stooq.com/q/d/l/?s=vt.us&i=d'; // 備援：日線 CSV (無 Adj Close)

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

function fetch_yahoo_csv($historyPage, $downloadUrl)
{
    $cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'yahoo_cookie_' . uniqid() . '.txt';
    // 1) 先造訪歷史頁，取得 Cookie
    [$http1, $html, $err1] = curl_get_with_cookies($historyPage, $cookieFile);
    // 2) 帶 Cookie 下載 CSV
    [$http2, $csv, $err2] = curl_get_with_cookies($downloadUrl, $cookieFile, $historyPage);
    @unlink($cookieFile);
    return [$http2, $csv, $err2];
}

// 嘗試 Yahoo
[$yHttp, $yData, $yErr] = fetch_yahoo_csv($yahooHistoryPage, $yahooDownloadUrl);

// 若 Yahoo 失敗（例如 401），用 Stooq 備援（注意：無 Adj Close）
$useStooq = false;
if ($yHttp !== 200 || $yData === false || stripos($yData, 'Date,Open,High,Low,Close') !== 0) {
    $useStooq = true;
}

$lastPerYear = [];

if ($useStooq) {
    // === 下載 Stooq CSV ===
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $stooqUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
            'Accept: text/csv,*/*;q=0.8',
        ],
    ]);
    $sData = curl_exec($ch);
    $sHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $sErr  = curl_error($ch);
    curl_close($ch);

    if ($sHttp !== 200 || $sData === false) {
        echo "下載失敗 (Yahoo HTTP $yHttp / Stooq HTTP $sHttp)\n";
        exit(1);
    }

    // 去掉 UTF-8 BOM
    $sData = preg_replace('/^\xEF\xBB\xBF/', '', $sData);

    // 解析 Stooq CSV（常見標頭：Date,Open,High,Low,Close,Volume）
    $fp = fopen('php://memory', 'r+');
    fwrite($fp, $sData);
    rewind($fp);

    $header = fgetcsv($fp);
    if (!$header) {
        echo "Stooq CSV 為空或無法解析\n";
        exit(1);
    }
    $headerLower = array_map(function ($h) {
        return strtolower(trim($h));
    }, $header);
    $idx = array_flip($headerLower);

    if (!isset($idx['date']) || !isset($idx['close'])) {
        echo "Stooq CSV 欄位不足（找不到 date/close）\n";
        exit(1);
    }

    while (($row = fgetcsv($fp)) !== false) {
        if (!$row) continue;
        $isEmpty = true;
        foreach ($row as $cell) {
            if (trim($cell) !== '') {
                $isEmpty = false;
                break;
            }
        }
        if ($isEmpty) continue;

        $rowAssoc = [];
        foreach ($header as $i => $colName) {
            $key = strtolower(trim($colName));
            $rowAssoc[$key] = isset($row[$i]) ? trim($row[$i]) : '';
        }

        $date = $rowAssoc['date'] ?? '';
        $closeStr = $rowAssoc['close'] ?? '';
        if ($date === '' || $closeStr === '' || strtolower($closeStr) === 'null') continue;

        $ts = strtotime($date);
        if ($ts === false) continue;
        $year = (int)date('Y', $ts);

        if (!isset($lastPerYear[$year]) || $ts > strtotime($lastPerYear[$year]['Date'])) {
            $lastPerYear[$year] = [
                'Date'      => $date,
                'Close'     => (float)$closeStr,
                'Adj Close' => null, // Stooq 無還原價
            ];
        }
    }
    fclose($fp);
} else {
    // === 解析 Yahoo CSV：Date,Open,High,Low,Close,Adj Close,Volume ===
    $fp = fopen('php://memory', 'r+');
    fwrite($fp, $yData);
    rewind($fp);

    $header = fgetcsv($fp);
    $idx = array_flip($header);
    foreach (['Date', 'Close', 'Adj Close'] as $col) {
        if (!isset($idx[$col])) {
            echo "Yahoo CSV 欄位缺少：$col\n";
            exit(1);
        }
    }

    while (($row = fgetcsv($fp)) !== false) {
        if (count($row) < count($header)) continue;
        $rec = array_combine($header, $row);
        if (empty($rec['Date']) || $rec['Close'] === 'null') continue;

        $date = $rec['Date'];
        $ts   = strtotime($date);
        $year = (int)date('Y', $ts);

        if (!isset($lastPerYear[$year]) || $ts > strtotime($lastPerYear[$year]['Date'])) {
            $lastPerYear[$year] = $rec;
        }
    }
    fclose($fp);
}

// 只取最近 30 年（VT 會少於 30，沒關係）
$years = array_keys($lastPerYear);
sort($years);
$years = array_slice($years, -30);

// 組結果
$result = [];
foreach ($years as $y) {
    $r = $lastPerYear[$y];
    $close     = isset($r['Close']) ? (float)$r['Close'] : null;
    $adjClose  = isset($r['Adj Close']) && $r['Adj Close'] !== '' ? (float)$r['Adj Close'] : null;

    $result[] = [
        'year'       => $y,
        'date'       => $r['Date'] ?? '',
        'close'      => is_numeric($close) ? round($close, 2) : null,
        'adj_close'  => is_numeric($adjClose) ? round($adjClose, 2) : null,
    ];
}

// 年報酬率 (以上一年為基準)
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

    $returnClose = (is_numeric($prevClose) && $prevClose != 0 && is_numeric($curClose)) ? ($curClose - $prevClose) / $prevClose * 100 : null;
    $returnAdj   = (is_numeric($prevAdj)   && $prevAdj   != 0 && is_numeric($curAdj))   ? ($curAdj   - $prevAdj)   / $prevAdj   * 100 : null;

    $result[$i]['return_close']     = is_null($returnClose) ? null : round($returnClose, 2) . "%";
    $result[$i]['return_adj_close'] = is_null($returnAdj)   ? null : round($returnAdj, 2) . "%";
}

// CLI 表格輸出
echo ($useStooq ? "[備援 Stooq 資料：Adj Close 皆為 null]\n" : "");
echo "VT 近 30 年：每年最後交易日收盤價與報酬率（Close / Adj Close）\n";
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

// 年化報酬率 (CAGR) — 用第一年與最後一年；資料不足時回 null
$N = count($result) - 1;
if ($N > 0) {
    $firstClose = $result[0]['close'];
    $lastClose  = $result[$N]['close'];
    $firstAdj   = $result[0]['adj_close'];
    $lastAdj    = $result[$N]['adj_close'];

    $cagrClose = (is_numeric($firstClose) && is_numeric($lastClose) && $firstClose > 0)
        ? pow($lastClose / $firstClose, 1 / $N) - 1
        : null;
    $cagrAdj = (is_numeric($firstAdj) && is_numeric($lastAdj) && $firstAdj > 0)
        ? pow($lastAdj / $firstAdj, 1 / $N) - 1
        : null;
} else {
    $cagrClose = null;
    $cagrAdj   = null;
}

echo "\n=== 年化報酬率 (CAGR) ===\n";
echo "Close CAGR     : " . (is_null($cagrClose) ? 'null' : round($cagrClose * 100, 2) . "%") . "\n";
echo "Adj Close CAGR : " . (is_null($cagrAdj) ? 'null' : round($cagrAdj * 100, 2) . "%") . "\n";

// JSON 輸出
$jsonPath = __DIR__ . '/vt_last_trading_day_30y.json';
$output = [
    'data' => $result,
    'cagr' => [
        'close' => is_null($cagrClose) ? null : round($cagrClose * 100, 2) . "%",
        'adj_close' => is_null($cagrAdj) ? null : round($cagrAdj * 100, 2) . "%"
    ],
    'source' => $useStooq ? 'stooq' : 'yahoo',
    'ticker' => $ticker
];
file_put_contents($jsonPath, json_encode($output, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "\nJSON 已輸出：$jsonPath\n";

// CSV 輸出
$csvPath = __DIR__ . '/vt_last_trading_day_30y.csv';
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
