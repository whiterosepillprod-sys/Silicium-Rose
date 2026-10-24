<?php
// Mesure d'audience anonyme de Silicium Rose (24/10/2026) : ni cookie, ni stockage dans le navigateur,
// aucune adresse IP ni empreinte. On garde seulement, par jour et par page : le nombre de vues,
// le domaine qui a envoyé le lecteur (x.com, t.co, google...) et le paramètre utm_source/utm_content.
// Les compteurs sont écrits HORS de la racine publique du site.
header('Cache-Control: no-store');
$DOSSIER = dirname(__DIR__) . '/compteur-silicium';
$JETON_SHA256 = '072848144fd3a0b6542ef1bf4efc324284516168a986e29ec90041b26f810205'; // empreinte du jeton de lecture (le jeton lui-même n'est pas dans le code)

if (isset($_GET['lire'])) {
    if (!hash_equals($JETON_SHA256, hash('sha256', (string)$_GET['lire']))) { http_response_code(403); exit; }
    $mois = preg_match('/^\d{4}-\d{2}$/', $_GET['mois'] ?? '') ? $_GET['mois'] : gmdate('Y-m');
    header('Content-Type: application/json; charset=utf-8');
    $f = "$DOSSIER/$mois.json";
    echo is_file($f) ? file_get_contents($f) : '{}';
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(204); exit; }
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
if ($ua === '' || preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|curl|wget|python|headless/i', $ua)) { http_response_code(204); exit; }
$p = substr((string)($_POST['p'] ?? ''), 0, 200);
if (!preg_match('#^/[a-z0-9/_.-]*$#', $p)) { http_response_code(204); exit; }
if ($p === '/') $p = '/index.html';
$r = strtolower(substr((string)($_POST['r'] ?? ''), 0, 100)); if (!preg_match('/^[a-z0-9.-]*$/', $r)) $r = '';
$s = strtolower(substr((string)($_POST['s'] ?? ''), 0, 40)); if (!preg_match('/^[a-z0-9_.-]*$/', $s)) $s = '';
$c = strtolower(substr((string)($_POST['c'] ?? ''), 0, 40)); if (!preg_match('/^[a-z0-9_.@-]*$/', $c)) $c = '';
if (!is_dir($DOSSIER)) { @mkdir($DOSSIER, 0700, true); }
$jour = gmdate('Y-m-d');
$h = @fopen("$DOSSIER/" . gmdate('Y-m') . '.json', 'c+');
if (!$h) { http_response_code(204); exit; }
flock($h, LOCK_EX);
$d = json_decode(stream_get_contents($h) ?: '{}', true) ?: [];
$e = $d[$jour][$p] ?? ['vues' => 0, 'referents' => [], 'utm' => []];
$e['vues']++;
$cr = $r === '' ? '(direct)' : $r;
$e['referents'][$cr] = ($e['referents'][$cr] ?? 0) + 1;
if ($s !== '') { $k = $s . ($c !== '' ? '/' . $c : ''); $e['utm'][$k] = ($e['utm'][$k] ?? 0) + 1; }
$d[$jour][$p] = $e;
ftruncate($h, 0); rewind($h); fwrite($h, json_encode($d)); fflush($h);
flock($h, LOCK_UN); fclose($h);
http_response_code(204);
