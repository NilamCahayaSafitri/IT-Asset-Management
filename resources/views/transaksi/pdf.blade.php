<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $d['ref'] }}</title>
    <style>
        @page { size: A4 portrait; margin: 9.9mm; }
        body { margin: 0; font-family: "Times New Roman", Times, serif; font-size: 9pt; line-height: 1.18; color: #000; }
        .ea-head { height: 40px; }
        .ea-logo-l { float: left; height: 38px; }
        .ea-logo-r { float: right; height: 38.7px; }
        .ea-cap { clear: both; font-weight: bold; font-size: 9pt; margin: 10pt 0 2pt; }
        .ea-title { font-weight: bold; font-size: 17pt; text-align: center; margin-bottom: 4pt; }
        .ea-title span { text-decoration: underline; }
        table.ea-t { border-collapse: collapse; width: 100%; table-layout: fixed; font-size: 9pt; }
        table.ea-t tr td { border: .5pt solid #000; padding: 1.2pt 3pt; vertical-align: middle; font-size: 9pt; }
        table.ea-t td.sh { background: #f2f2f2; }
        table.ea-t tr td.nob { border: none; }
        table.ea-t tr td.refcell { padding: 0 0 1.5pt 0; vertical-align: bottom; }
        .ea-ref { border: .5pt solid #000; padding: 5pt 4pt; font-weight: bold; font-size: 10pt; }
        .ea-ref .v { font-weight: normal; }
        td.b { font-weight: bold; } td.i { font-style: italic; } td.c { text-align: center; }
        td.big { font-size: 10pt; } td.big2 { font-size: 9.5pt; }
        .ul { font-weight: bold; text-decoration: underline; }
        .n { font-weight: normal; }
        .nb { font-weight: normal; font-style: italic; font-size: 8pt; }
        .nb2 { font-weight: normal; font-style: normal; }
        .v { font-weight: normal; font-style: normal; }
        .tk { font-family: "DejaVu Sans", sans-serif; font-weight: bold; }
        .ea-gap { height: 7pt; }
        table.ea-eq tr.hd td { font-size: 8.5pt; }
        table.ea-eq tr.dr td { height: 23pt; font-size: 8.5pt; text-align: center; }
        .ea-fn { font-style: italic; font-size: 8pt; text-align: right; margin: 2pt 0 8pt; }
        .ea-rem { margin: 10pt 0 3pt; font-size: 9.5pt; }
        .ea-rules .rl { height: 16.25pt; border-bottom: .75pt solid #000; }
        .ea-remtxt { margin: -51pt 0 12pt; min-height: 51pt; line-height: 17pt; font-size: 9.5pt; padding: 0 2pt; }
        .ea-resp-t { font-weight: bold; text-decoration: underline; font-size: 9.5pt; margin-bottom: 2pt; }
        table.ea-resp tr td { font-size: 8.5pt; text-align: justify; vertical-align: top; padding: 2pt 3pt; }
        table.ea-resp ol { margin: 0; padding-left: 23pt; }
        table.ea-resp li { padding-left: 2pt; margin-bottom: 1pt; }
        table.ea-sig tr td { vertical-align: top; font-size: 9pt; }
        table.ea-sig .sg { height: 14pt; }
        table.ea-sig .sgl { margin-left: 28%; width: 43%; height: 7pt; border-bottom: .75pt solid #000; }
        table.ea-sig .sgc { height: 12pt; }
        table.ea-cm { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.ea-t table.ea-cm tr td { border: none; padding: 0; font-weight: normal; font-style: normal; font-size: 9pt; vertical-align: bottom; }
        table.ea-t table.ea-cm tr td.m { border-bottom: .75pt solid #000; }
        table.ea-t table.ea-cm tr td.l { padding-right: 3pt; }
        table.ea-t table.ea-cm tr td.r { text-align: right; padding-left: 3pt; }
        table.ea-sig .sg2 { margin: 6pt 0; }
        table.ea-foot-t { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 10pt; }
        table.ea-foot-t td { border: none; padding: 0; font-style: italic; font-size: 8pt; vertical-align: top; }
        table.ea-foot-t td.f0 { width: 52%; }
        table.ea-foot-t td.f1 { width: 22%; }
        table.ea-foot-t td.f2 { width: 26%; text-align: right; }
        table.ea-sig, table.ea-resp { page-break-inside: avoid; }
        /* Mode rapat: dipakai otomatis bila surat tidak muat satu halaman */
        body.compact table.ea-eq tr.dr td { height: 19pt; }
        body.compact .ea-gap { height: 4pt; }
        body.compact .ea-cap { margin-top: 6pt; }
        body.compact .ea-fn { margin-bottom: 4pt; }
        body.compact .ea-rem { margin-top: 6pt; }
        body.compact .ea-remtxt { margin-bottom: 6pt; }
        body.compact table.ea-foot-t { margin-top: 6pt; }
        tr.ruler td { border: none !important; padding: 0 !important; height: 0; font-size: 0; line-height: 0; }
        sup { font-size: 6.5pt; line-height: 0; vertical-align: super; font-style: normal; }
    </style>
</head>
<body class="{{ ($compact ?? false) ? 'compact' : '' }}">
    @include('transaksi._surat', ['d' => $d, 'pdf' => true])
</body>
</html>
