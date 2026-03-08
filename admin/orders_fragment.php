<?php
// orders_fragment.php - renders the orders grid only (used by admin/orders.php via include and AJAX polling)
session_start();
if(!isset($_SESSION['admin'])){
    http_response_code(403);
    exit('Forbidden');
}

$ordersFile = __DIR__ . '/../orders.json';
$orders = [];
if(file_exists($ordersFile)){
    $od = json_decode(file_get_contents($ordersFile), true);
    if(is_array($od)) $orders = $od;
}

// helper functions
function bucket_for_order($o){
    $date = $o['created_at'] ?? ($o['date'] ?? null);
    if(!$date) return 'older';
    $ts = @strtotime($date);
    if(!$ts) return 'older';
    $days = (time() - $ts) / 86400;
    if($days < 1) return 'today';
    if($days < 2) return 'yesterday';
    if($days < 7) return 'last7';
    if($days < 30) return 'last30';
    return 'older';
}
function human_time_diff($date){
    $ts = @strtotime($date);
    if(!$ts) return '';
    $diff = time() - $ts;
    if($diff < 60) return $diff . ' seconds ago';
    if($diff < 3600) return floor($diff/60) . ' minutes ago';
    if($diff < 86400) return floor($diff/3600) . ' hours ago';
    $days = floor($diff/86400);
    if($days == 1) return '1 day ago';
    if($days < 7) return $days . ' days ago';
    if($days < 30) return ceil($days/7) . ' weeks ago';
    return ceil($days/30) . ' months ago';
}
function resolve_img_url($img){
    $img = trim($img);
    if(!$img) return '../images/placeholder.png';
    if(preg_match('#^https?://#i', $img)) return $img;
    // try site-root relative (one level up from admin)
    $candidate = __DIR__ . '/../' . ltrim($img, '/\\');
    if(file_exists($candidate)) return '../' . ltrim($img, '/\\');
    // try admin-relative
    $candidate2 = __DIR__ . '/' . ltrim($img, '/\\');
    if(file_exists($candidate2)) return $img;
    return '../images/placeholder.png';
}

$unattended = [];
$attended = [];
foreach($orders as $idx => $o){
    $o['__index'] = $idx;
    if(!isset($o['attended']) || !$o['attended']) $unattended[] = $o; else $attended[] = $o;
}

$bucketLabels = [
    'today' => 'Today',
    'yesterday' => 'Yesterday',
    'last7' => 'Last 7 days',
    'last30' => 'Last 30 days',
    'older' => 'Older'
];

$unattBuckets = array_fill_keys(array_keys($bucketLabels), []);
foreach($unattended as $o){ $k = bucket_for_order($o); $unattBuckets[$k][] = $o; }
$attBuckets = array_fill_keys(array_keys($bucketLabels), []);
foreach($attended as $o){ $k = bucket_for_order($o); $attBuckets[$k][] = $o; }

// render grid
?>
<div class="grid md:grid-cols-2 gap-6">
  <div class="max-h-[70vh] overflow-y-auto p-2">
    <div class="flex items-center justify-between">
      <h3 class="font-bold mb-2">Unattended Orders (<?= count($unattended) ?>)</h3>
    </div>
    <?php foreach($bucketLabels as $bkey => $label):
      $list = $unattBuckets[$bkey] ?? [];
      if(empty($list)) continue;
    ?>
      <div class="mt-4">
        <div class="flex justify-between items-center">
          <h4 class="font-semibold"><?= htmlspecialchars($label) ?> (<?= count($list) ?>)</h4>
        </div>
        <?php foreach($list as $o): ?>
          <div class="border rounded p-3 mb-3 bg-white shadow-sm">
            <div class="flex gap-4">
              <div style="width:96px;flex-shrink:0">
                <?php
                  $items = [];
                  if(isset($o['cart']) && is_array($o['cart'])) $items = $o['cart'];
                  elseif(isset($o['items']) && is_array($o['items'])) $items = $o['items'];
                  $firstImg = '';
                  if(!empty($items)) $firstImg = $items[0]['image'] ?? '';
                  $imgUrl = resolve_img_url($firstImg);
                ?>
                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:6px">
              </div>
              <div class="flex-1">
                <div class="flex justify-between items-start">
                  <div>
                    <p class="font-bold">Order #<?= htmlspecialchars($o['__index']) ?> — <?= htmlspecialchars($o['name'] ?? '') ?></p>
                    <?php $odate = $o['created_at'] ?? ($o['date'] ?? ''); ?>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($o['email'] ?? '') ?> — <?= htmlspecialchars($odate) ?> <span class="text-xs text-gray-400">(<?= human_time_diff($odate) ?>)</span></p>
                  </div>
                  <div class="text-right">
                    <a href="attend_order.php?id=<?= urlencode($o['__index']) ?>&return=orders" class="bg-green-600 text-white px-3 py-1 rounded">Mark attended</a>
                  </div>
                </div>
                <div class="mt-3 text-sm">
                  <table class="w-full text-sm border-collapse">
                    <thead>
                      <tr class="bg-gray-100"><th class="p-2 text-left">Product</th><th class="p-2 text-right">Unit</th><th class="p-2 text-center">Qty</th><th class="p-2 text-right">Subtotal</th></tr>
                    </thead>
                    <tbody>
                      <?php $subtotal = 0; foreach($items as $it): $u = floatval($it['price'] ?? 0); $q = intval($it['qty'] ?? 0); $s = $u*$q; $subtotal += $s; ?>
                        <tr>
                          <td class="p-2"><?= htmlspecialchars($it['name'] ?? '') ?></td>
                          <td class="p-2 text-right">₦<?= number_format($u) ?></td>
                          <td class="p-2 text-center"><?= $q ?></td>
                          <td class="p-2 text-right">₦<?= number_format($s) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <div class="mt-2 text-sm"><strong>Total:</strong> ₦<?= number_format($o['total'] ?? $subtotal) ?></div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="max-h-[50vh] overflow-y-auto p-2">
    <h3 class="font-bold mb-2">Attended Orders (<?= count($attended) ?>)</h3>
    <?php foreach($bucketLabels as $bkey => $label):
      $list = $attBuckets[$bkey] ?? [];
      if(empty($list)) continue;
    ?>
      <div class="mt-4">
        <h4 class="font-semibold"><?= htmlspecialchars($label) ?> (<?= count($list) ?>)</h4>
        <?php foreach($list as $o): ?>
          <div class="border rounded p-3 mb-3 bg-gray-50">
            <div class="flex gap-4">
              <div style="width:96px;flex-shrink:0">
                <?php
                  $items = [];
                  if(isset($o['cart']) && is_array($o['cart'])) $items = $o['cart'];
                  elseif(isset($o['items']) && is_array($o['items'])) $items = $o['items'];
                  $firstImg = '';
                  if(!empty($items)) $firstImg = $items[0]['image'] ?? '';
                  $imgUrl = resolve_img_url($firstImg);
                ?>
                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:6px">
              </div>
              <div class="flex-1">
                <p class="font-bold">Order #<?= htmlspecialchars($o['__index']) ?> — <?= htmlspecialchars($o['name'] ?? '') ?></p>
                <?php $odate = $o['created_at'] ?? ($o['date'] ?? ''); ?>
                <p class="text-sm text-gray-600"><?= htmlspecialchars($o['email'] ?? '') ?> — <?= htmlspecialchars($odate) ?> <span class="text-xs text-gray-400">(<?= human_time_diff($odate) ?>)</span></p>
                <div class="mt-3 text-sm">
                  <table class="w-full text-sm border-collapse">
                    <thead>
                      <tr class="bg-gray-100"><th class="p-2 text-left">Product</th><th class="p-2 text-right">Unit</th><th class="p-2 text-center">Qty</th><th class="p-2 text-right">Subtotal</th></tr>
                    </thead>
                    <tbody>
                      <?php $subtotal = 0; foreach($items as $it): $u = floatval($it['price'] ?? 0); $q = intval($it['qty'] ?? 0); $s = $u*$q; $subtotal += $s; ?>
                        <tr>
                          <td class="p-2"><?= htmlspecialchars($it['name'] ?? '') ?></td>
                          <td class="p-2 text-right">₦<?= number_format($u) ?></td>
                          <td class="p-2 text-center"><?= $q ?></td>
                          <td class="p-2 text-right">₦<?= number_format($s) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <div class="mt-2 text-sm"><strong>Total:</strong> ₦<?= number_format($o['total'] ?? $subtotal) ?></div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
