<?php
header("Access-Control-Allow-Origin: *"); 
/*php tarafının yazmaya başladığımda bu kısım yoktu sonrasında 
frontende verileri alamayınca ufak bir araştırma sonucunda cors ayarları yapmam 
gerektiğini anladım bu satırla birlikte bu dosyaya her yerden erişim izni verdim

*/

header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); 
/*
header("Access-Control-Allow-Origin: *"); bu kod ile erişim izinlerini verdik ama
bizim yapmak istediğimiz işlemlerede izin vermemiz gerekiyor Get,post gibi
bu kod ile beraber GET, POST, OPTIONS işlemlerine izin verdim
*/

header("Access-Control-Allow-Headers: Content-Type"); 
/*
 İzin verilen içerik tipleri başlıklar ve gelen verilerin nasıl bir formatta olduğunu anlamamı sağlıyor 
 */


error_reporting(E_ALL);
ini_set('display_errors', 1);
/*
kodda bir hata varsa göstermesi için bullandım neyin yanlış gittiğini anlamak için 
*/
header('Content-Type: application/json');
/*
cevapların json formatta olacağını belirttim

*/



$apiUrl = "https://case-test-api.humanas.io";
$response = file_get_contents($apiUrl);
/*apiden gelen verilerin alınması alınan veriler response değişkenine aktarılıyor*/


if ($response === false) {
    echo json_encode(['error' => 'API erişim hatası']);
    exit;
}
/*
eğer veri alınamaz yada bir hata alırsam bir şekilde bağlantının başarısız olduğu durumda
işlem dursun 
*/

$data = json_decode($response, true);
/*
gelen veriyi oku ve json olarak çöz ve dizi haline getir
*/


if (!isset($data['data']['rows']) || !is_array($data['data']['rows'])) {
    echo json_encode(['error' => 'Veri beklenen formatta değil']);
    exit;
}
/*gelen veriyi kontrol ediyorum gelen json içinde rows ve data varmı diye kontrol ediyorum eğer veri yoksa beklenen veri hatalı diyerek geri dönüş yapıyorum cors ayarlarını yaparken buradaki if koşulu yardımcı oldu bana çünkü php sunucusunu çalıştırdığımda veriler geliyordu ama frontende veri beklenen formattda değil yazısı dönmüştü sonrasında cors yapılandırması işimi çözdü */

$users = $data['data']['rows'];
$results = [];

/*
gelen tüm datayı users değişkeni içinde barındırdım 
tahmin sonuçları için results değişkeni oluşturdum
*/

foreach ($users as $user) {
    if (!isset($user['logins']) || !is_array($user['logins']) || empty($user['logins'])) {
        $results[] = [
            'id' => $user['id'],
            'name' => $user['name'] ?? 'Unknown',
            'error' => 'Geçersiz veya eksik login verisi'
        ];
        continue;
        /*
        her kullanıcı için yapılan kontrolde gelen veride eksik data varsa o veriyi atla
        */
    }

    $logins = array_map('strtotime', $user['logins']);
    sort($logins);
/*
gelen verideki büün tarihleri zamana çevir ve sırala 
 
 */


    
    $intervals = [];
    for ($i = 1; $i < count($logins); $i++) {
        $intervals[] = $logins[$i] - $logins[$i - 1];
    }
/*
login zamanları arasındaki farkın hesaplanması saniye olarak
*/
    $avgInterval = !empty($intervals) ? array_sum($intervals) / count($intervals) : 0;
   $prediction1 = (int)(end($logins) + $avgInterval);
/*
şimdi birinci tahmin burada başlıyor gelen veride belli tarih zaman aralıklarında giriş yapılmış kendimden örnek veermek gerekirse belirle işim olmadığı zamanlar farazi konşuyorum 1 saatte 1 instagrama giriyorum binevi bir el alışkanlığı gibi bundan yola çıkarak giriş zamanlarının ortalamasını alıp en son girişin üstüne ekleyipbir sonraki giriş tarihini tahmin ediyorum büyük ihtimalle bu case yapan herkesde bunu düşünmüştür.
 */

    
    $hours = array_map(fn($t) => date('H:i', $t), $logins);
    //tüm giriş saatlerini belirli bir formata çevir
    $mostCommonHour = array_count_values($hours);    
    arsort($mostCommonHour);
    $mostCommon = key($mostCommonHour);
    //en çok hangi saatlerde girilmiş
    $nextDay = strtotime('+1 day', end($logins));
    $prediction2 = strtotime(date('Y-m-d', $nextDay) . " $mostCommon");
    /*
    ikinci tahminde burada başlıyor mesela adam işten çıktı yada işi bitti telefonu alıyor eline bi bakıyor en azından bende öyle bende son girşin ertes gününü ikinci tahmin olarak aldım
    */

    $results[] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'last_login' => date('c', end($logins)),
        'prediction_avg_interval' => date('c', $prediction1),
        'prediction_common_hour' => date('c', $prediction2),
    ];
    /*
    sonrasında her kullanıcı için son giriş zamanını ver ortalamayı frontende listeliceğim için verileri alıp buna göre tablolamak  amacıyla verilerin son halini alıyorum
    */
    
}

echo json_encode($results, JSON_PRETTY_PRINT);
//büttün verileri ekrana yazdır

// http://localhost/login-predictor/backend/predictions.php