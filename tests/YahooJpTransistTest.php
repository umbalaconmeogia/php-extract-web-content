<?php
include '../src/YahooJpTransist.php';
mb_internal_encoding("UTF-8");

$yahooJpTransist = new YahooJpTransist('http://transit.yahoo.co.jp/search/print?from=%E4%BA%AC%E7%8E%8B%E7%A8%B2%E7%94%B0%E5%A0%A4&flatlon=&to=%E6%9D%B1%E4%BA%AC&tlatlon=&viacode=&ym=201606&y=2016&m=06&d=05&hh=07&m1=1&m2=6&shin=1&ex=1&hb=1&al=1&lb=1&sr=1&type=1&ws=2&s=0&ei=&fl=1&tl=3&expkind=1&mtf=&out_y=&mode=&c=&searchOpt=&stype=&ticket=ic&userpass=0&passtype=&detour_id=&no=1');
echo $yahooJpTransist;
?>