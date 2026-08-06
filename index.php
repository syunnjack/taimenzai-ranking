<?php

declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/ContentSafetyFilter.php';
require __DIR__ . '/DmmClient.php';

$client = new DmmClient(DMM_API_ID, DMM_AFFILIATE_ID, __DIR__ . '/cache');

$siteName = '対面座位ランキング';
$keyword = '対面座位';
$fetchFailed = false;
$items = [];

try {
    $raw = $client->fetchItems($keyword, 60);
    $items = array_values(array_filter($raw, static fn (array $i): bool => ContentSafetyFilter::isSafe($i['title'], $i['maker'])));
} catch (Throwable $e) {
    $fetchFailed = true;
}

$lastUpdated = date('Y/m/d H:i');
$gaId = defined('GA4_MEASUREMENT_ID') ? GA4_MEASUREMENT_ID : '';

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>対面座位ランキング｜レビュー評価の高いFANZA人気作品まとめ</title>
<meta name="description" content="FANZAの対面座位ジャンルをレビュー評価・件数順に自動集計。実際の評価データをもとにしたランキングで作品を探せます。18歳未満閲覧禁止。">
<link rel="canonical" href="https://<?= h($_SERVER['HTTP_HOST'] ?? '') ?><?= h($_SERVER['REQUEST_URI'] ?? '/') ?>">
<meta property="og:site_name" content="<?= h($siteName) ?>">
<meta property="og:type" content="website">
<script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => $siteName], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php if ($gaId): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= h($gaId) ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?= h($gaId) ?>');
</script>
<?php endif; ?>
<style>
:root { --ink:#eaf0f2; --bg:#0e181a; --panel:#182629; --accent:#2ab0a8; --accent2:#e0925a; --line:#2a4245; --muted:#8fa8ac; }
* { box-sizing:border-box; }
body { margin:0; font-family:"Hiragino Sans","Yu Gothic",sans-serif; color:var(--ink); background:var(--bg); line-height:1.7; }
a { color:var(--accent); }
.gate { position:fixed; inset:0; background:#0f0c08; display:flex; align-items:center; justify-content:center; padding:20px; z-index:20; }
.gate section { max-width:420px; text-align:center; background:var(--panel); border:1px solid var(--line); border-radius:14px; padding:40px 28px; }
.gate .badge { display:inline-block; background:var(--accent); color:#1b1712; font-weight:900; padding:4px 14px; border-radius:20px; font-size:.8rem; margin-bottom:16px; }
.gate h1 { font-size:1.3rem; margin:10px 0; }
.gate p { color:var(--muted); font-size:.9rem; }
.gate button { margin-top:18px; background:var(--accent); color:#1b1712; border:0; padding:12px 28px; border-radius:8px; font-weight:800; cursor:pointer; }
.gate a.exit { display:block; margin-top:12px; color:var(--muted); font-size:.85rem; }
header { display:flex; align-items:center; justify-content:space-between; padding:16px 24px; border-bottom:1px solid var(--line); position:sticky; top:0; background:rgba(27,23,18,.92); backdrop-filter:blur(6px); z-index:5; }
.logo { font-weight:900; text-decoration:none; color:var(--ink); font-size:1.1rem; }
.logo span { color:var(--accent); }
.age-badge { font-size:.7rem; border:1px solid var(--accent); color:var(--accent); padding:3px 10px; border-radius:20px; }
.wrap { max-width:1080px; margin:0 auto; padding:0 20px; }
.hero { padding:44px 0 28px; }
.eyebrow { color:var(--accent2); font-weight:800; font-size:.78rem; letter-spacing:.1em; }
h1.main { font-size:1.9rem; line-height:1.4; margin:10px 0; }
h1.main em { color:var(--accent); font-style:normal; }
.lead { color:var(--muted); max-width:640px; font-size:.95rem; }
.updated { margin-top:14px; font-size:.8rem; color:var(--muted); }
.updated b { color:var(--accent2); }
section { padding:20px 0 40px; }
.grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:16px; }
.card { background:var(--panel); border:1px solid var(--line); border-radius:12px; overflow:hidden; display:flex; flex-direction:column; }
.card img { width:100%; aspect-ratio:3/2; object-fit:cover; background:#0f0c08; }
.card .body { padding:14px; display:flex; flex-direction:column; gap:6px; flex:1; }
.card .rank { position:relative; }
.card .review-badge { position:absolute; top:8px; left:8px; background:var(--accent2); color:#1b1712; font-weight:900; font-size:.78rem; padding:4px 10px; border-radius:20px; }
.card .title { font-size:.86rem; line-height:1.5; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:2.6em; }
.card .meta { font-size:.75rem; color:var(--muted); }
.card .price { margin-top:auto; color:var(--accent2); font-weight:900; font-size:1rem; }
.card a.go { display:block; text-align:center; background:var(--accent); color:#1b1712; font-weight:800; padding:9px; border-radius:0 0 10px 10px; text-decoration:none; font-size:.85rem; }
.empty { color:var(--muted); text-align:center; padding:60px 0; }
footer { padding:30px 0 50px; color:var(--muted); font-size:.72rem; border-top:1px solid var(--line); }
footer p { margin:6px 0; }
</style>
</head>
<body>
<div class="gate" id="age-gate">
<section>
<span class="badge">18+</span>
<h1>年齢確認</h1>
<p>このサイトは対面座位ジャンル(FANZAアフィリエイト)のレビューランキング情報を扱います。18歳未満の方はご利用いただけません。</p>
<button onclick="document.getElementById('age-gate').style.display='none'">18歳以上です</button>
<a class="exit" href="https://www.google.com/">退出する</a>
</section>
</div>

<header>
<a href="#top" class="logo">対面座位<span>ランキング</span></a>
<span class="age-badge">18歳以上限定</span>
</header>

<div class="wrap">
<section class="hero" id="top">
<p class="eyebrow">TACHIBACK GENRE RANKING</p>
<h1 class="main">レビュー評価の高い順で、<br><em>対面座位</em>作品を探す。</h1>
<p class="lead">FANZA公式アフィリエイトAPIから取得した実際のレビュー評価・件数をもとに、対面座位ジャンルをランキング形式で紹介しています(3件以上のレビューがある作品のみ掲載)。</p>
<p class="updated">最終更新: <b><?= h($lastUpdated) ?></b>(1時間キャッシュ・自動更新)</p>
</section>

<section>
<?php if ($fetchFailed || empty($items)): ?>
<p class="empty">現在ランキング情報を準備中です。しばらくしてから再度ご確認ください。</p>
<?php else: ?>
<div class="grid">
<?php foreach ($items as $item): ?>
<div class="card">
<div class="rank">
<span class="review-badge">★<?= h(number_format($item['reviewAverage'], 1)) ?> (<?= (int) $item['reviewCount'] ?>)</span>
<?php if ($item['imageURL']): ?>
<img src="<?= h($item['imageURL']) ?>" alt="<?= h($item['title']) ?>" loading="lazy">
<?php endif; ?>
</div>
<div class="body">
<div class="title"><?= h($item['title']) ?></div>
<div class="meta"><?= h($item['maker']) ?></div>
<?php if ($item['price']): ?>
<div class="price">¥<?= h(number_format((float) $item['price'])) ?></div>
<?php endif; ?>
</div>
<a class="go" href="<?= h($item['affiliateURL']) ?>" target="_blank" rel="sponsored noopener">FANZAで見る</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</section>
</div>

<footer>
<div class="wrap">
<p>本サイトはFANZAアフィリエイトプログラムを利用し、DMM.com公式APIから取得したレビュー情報をもとに構成しています。詳細は必ずリンク先の公式ページでご確認ください。</p>
<p>本サイトが紹介するリンクには広告(アフィリエイトリンク)を含みます。</p>
<p>&copy; <?= date('Y') ?> 対面座位ランキング</p>
</div>
</footer>
</body>
</html>
