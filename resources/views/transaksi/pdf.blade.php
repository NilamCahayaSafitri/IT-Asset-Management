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
        .ea-title { font-weight: bold; font-size: 17pt; text-align: center; border-bottom: .75pt solid #000; padding-bottom: 1pt; margin-bottom: 2pt; }
        table.ea-t { border-collapse: collapse; width: 100%; table-layout: fixed; font-size: 9pt; }
        table.ea-t tr td { border: .5pt solid #000; padding: 1.2pt 3pt; vertical-align: middle; font-size: 9pt; }
        table.ea-t td.sh { background: #f2f2f2; }
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
        .ea-rem { margin: 10pt 0 14pt; font-size: 9.5pt; min-height: 28pt; }
        .ea-resp-t { font-weight: bold; text-decoration: underline; font-size: 9.5pt; margin-bottom: 2pt; }
        table.ea-resp tr td { font-size: 8.5pt; text-align: justify; vertical-align: top; padding: 2pt 3pt; }
        table.ea-resp ol { margin: 0; padding-left: 23pt; }
        table.ea-resp li { padding-left: 2pt; margin-bottom: 1pt; }
        table.ea-sig tr td { vertical-align: top; font-size: 9pt; }
        table.ea-sig .sg { height: 14pt; }
        table.ea-sig .sg2 { margin: 6pt 0; }
        .ea-foot { font-style: italic; font-size: 8pt; margin-top: 14pt; }
        .ea-foot .l { margin-left: 52%; }
        .ea-foot .r { float: right; }
        tr.ruler td { border: none !important; padding: 0 !important; height: 0; font-size: 0; line-height: 0; }
        sup { font-size: 6.5pt; line-height: 0; vertical-align: super; font-style: normal; }
    </style>
</head>
<body>
    @include('transaksi._surat', ['d' => $d, 'pdf' => true])
</body>
</html>
