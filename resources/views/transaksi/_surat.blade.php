{{-- Replika template Word "Equipment Allocation Form". $d = Transaksi::suratData() --}}
@php
    // isi sel kosong -> &nbsp; (sama seperti eaVal di prototype)
    $v = fn ($s) => trim((string) $s) !== '' ? e($s) : '&nbsp;';
    // sel barang kosong -> "-"
    $item = fn ($s) => trim((string) $s) !== '' ? e($s) : '-';
    // CC dan Procurement Information (PO, AFE/CC, Year) kosong -> "-" (instruksi surat: jangan ada kolom kosong)
    $dash = $item;
    $types = $d['types'] ?? [];
    $tk = fn ($t) => in_array($t, $types, true) ? '<span class="tk">&#10003;</span> ' : '';
    $pad = function (array $rows, int $min) {
        while (count($rows) < $min) {
            $rows[] = null;
        }
        return $rows;
    };

    // Mode PDF (dompdf): lebar kolom lewat baris pengatur, karena <colgroup> tidak dibaca dompdf;
    // tanda {!! $s1 !!} {!! $s2 !!} {!! $s3 !!} memakai <sup>; logo disisipkan sebagai data URI.
    $pdf = $pdf ?? false;
    $cols = function (array $w) use ($pdf) {
        if ($pdf) {
            return '<tr class="ruler">'.collect($w)->map(fn ($x) => '<td style="width:'.$x.'%"></td>')->implode('').'</tr>';
        }
        return '<colgroup>'.collect($w)->map(fn ($x) => '<col style="width:'.$x.'%">')->implode('').'</colgroup>';
    };
    $g8  = $cols([16.25, 10.99, 10.99, 12.42, 10.99, 12.42, 12.9, 13.05]);
    $g10 = $cols([19.47, 10.81, 8.2, 8.49, 4.95, 11.74, 5.88, 8.96, 1.85, 19.65]);
    $s1 = $pdf ? '<sup>1)</sup>' : '¹⁾';
    $s2 = $pdf ? '<sup>2)</sup>' : '²⁾';
    $s3 = $pdf ? '<sup>3)</sup>' : '³⁾';
    $logoL = $pdf ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents(public_path('images/ea-logo-bpma.jpg'))) : asset('images/ea-logo-bpma.jpg');
    $logoR = $pdf ? 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/ea-logo-pge.png'))) : asset('images/ea-logo-pge.png');
@endphp

<div class="ea-head"><img class="ea-logo-l" src="{{ $logoL }}" alt="bpma"><img class="ea-logo-r" src="{{ $logoR }}" alt="PEMA Global Energi"></div>
<div class="ea-cap">PGE ICT Area Operations</div>
<div class="ea-title">EQUIPMENT ALLOCATION FORM</div>

<table class="ea-t">{!! $cols([62.1, 37.9]) !!}
    <tr><td class="b"><div class="ul">INSTRUCTIONS</div><div class="n">1.&nbsp; Fill the form completely</div><div class="n">2.&nbsp; Do not leave any space blank – put N/A or cross out if not applicable</div></td>
        <td class="b big">Reference No. : <span class="v">{{ $d['ref'] }}</span></td></tr>
</table>
<div class="ea-gap"></div>

<table class="ea-t">{!! $g10 !!}
    <tr><td colspan="2" class="b">Type of Allocation</td><td colspan="2" class="b c big">{!! $tk('Deploy') !!}Deploy</td><td colspan="2" class="b c big">{!! $tk('Return') !!}Return</td><td colspan="3" class="b c big">{!! $tk('Replace/Swap') !!}Replace/Swap</td><td class="b c big">{!! $tk('Move') !!}Move</td></tr>
    <tr><td class="b">User Name</td><td colspan="2">{!! $v($d['userName']) !!}</td><td colspan="2" class="b">User ID</td><td colspan="2">{!! $v($d['userId']) !!}</td><td class="b">CC</td><td colspan="2">{!! $dash($d['cc']) !!}</td></tr>
    <tr><td class="b">Department</td><td colspan="2">{!! $v($d['dept']) !!}</td><td colspan="2" class="b">Phone No</td><td colspan="2">{!! $v($d['phone']) !!}</td><td class="b">Room No</td><td colspan="2">{!! $v($d['room']) !!}</td></tr>
    <tr><td colspan="10" class="b i big2">Procurement Information &nbsp;<span class="nb">(for initial deployment only)</span></td></tr>
    <tr><td class="b">PO/Contract No:</td><td colspan="4">{!! $dash($d['po']) !!}</td><td colspan="2" class="b">AFE/CC:</td><td>{!! $dash($d['afe']) !!}</td><td colspan="2" class="b">Year <span class="nb">{!! $dash($d['year']) !!}</span></td></tr>
</table>
<div class="ea-gap"></div>

<table class="ea-t ea-eq">{!! $g8 !!}
    <tr><td colspan="8" class="b sh big">Deploy / Move Equipment {!! $s1 !!}</td></tr>
    <tr class="hd"><td class="b c sh">Equipment Name</td><td class="b c sh">Brand</td><td class="b c sh">Model</td><td class="b c sh">Serial No</td><td class="b c sh">Memory</td><td class="b c sh">Hard Disk</td><td class="b c sh">Asset No.</td><td class="b c sh">Asset Function {!! $s2 !!}</td></tr>
    @foreach ($pad($d['deploy'] ?? [], 4) as $r)
        @if ($r)
            <tr class="dr"><td>{!! $item($r['name']) !!}</td><td>{!! $item($r['brand']) !!}</td><td>{!! $item($r['model']) !!}</td><td>{!! $item($r['sn']) !!}</td><td>{!! $item($r['mem']) !!}</td><td>{!! $item($r['hdd']) !!}</td><td>{!! $v($r['code']) !!}</td><td>{!! $v($r['fn']) !!}</td></tr>
        @else
            <tr class="dr"><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        @endif
    @endforeach
</table>
<div class="ea-fn">{!! $s2 !!} Asset Function: a. Primary&nbsp; b. Shared&nbsp; c. Training&nbsp; d. Other (explain)</div>

<table class="ea-t ea-eq">{!! $g8 !!}
    <tr><td colspan="8" class="b sh big">Return Equipment? &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Reason: <span class="v">{{ $d['reason'] }}</span></td></tr>
    <tr class="hd"><td class="b c sh">Equipment Name</td><td class="b c sh">Brand</td><td class="b c sh">Model</td><td class="b c sh">Serial No</td><td class="b c sh">Memory</td><td class="b c sh">Hard Disk</td><td class="b c sh">Asset No.</td><td class="b c sh">Status {!! $s3 !!}</td></tr>
    @foreach ($pad($d['ret'] ?? [], 4) as $r)
        @if ($r)
            <tr class="dr"><td>{!! $item($r['name']) !!}</td><td>{!! $item($r['brand']) !!}</td><td>{!! $item($r['model']) !!}</td><td>{!! $item($r['sn']) !!}</td><td>{!! $item($r['mem']) !!}</td><td>{!! $item($r['hdd']) !!}</td><td>{!! $v($r['code']) !!}</td><td>{!! $v($r['status']) !!}</td></tr>
        @else
            <tr class="dr"><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        @endif
    @endforeach
</table>
<div class="ea-fn">{!! $s3 !!} Status: a. Deployable&nbsp; b. Surplus&nbsp; c. Waiting for Disposal&nbsp; c. Broken</div>

<div class="ea-rem"><b>Additional Remarks:</b> <span class="v">{{ $d['remarks'] }}</span></div>

<div class="ea-resp-t">User/Custodian's Responsibilities:</div>
<table class="ea-t ea-resp">{!! $cols([50, 50]) !!}
    <tr><td><ol start="3"><li>Do not remove any part/s (hard disk, CPU, monitor, memory etc.) as it is recorded under one asset number.</li><li>Inform Helpdesk when you have a user leaving your group.</li><li>Do not relocate / move or transfer the company equipment without notifying Helpdesk. Your name has been recorded as the custodian of the inventory list.</li></ol></td>
        <td><ol start="6"><li>For the Laptop users: Set the bit locker pin / harddisk password immediately when receiving it from EUS team. Keep the Laptop with you at all times when traveling and lock it up when not in use or when un-attended (use a Security cable lock or lock in cabinet/drawer).</li></ol></td></tr>
</table>
<div class="ea-gap"></div>

<table class="ea-t ea-sig">{!! $cols([33.33, 33.33, 33.34]) !!}
    <tr><td class="b i big2">PGE-Only</td><td class="b i big2 sh">User-Only</td><td class="b i big2">CMDB-Only</td></tr>
    <tr>
        <td class="b i"><div>Delivered / Received / Moved by {!! $s1 !!}</div><div class="nb2">Signature:</div><div class="sg">&nbsp;</div><div class="nb2">Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span class="v">{{ $d['pgeName'] }}</span></div><div class="nb2">Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span class="v">{{ $d['pgeDate'] }}</span></div></td>
        <td class="b i sh"><div>Received / Returned by {!! $s1 !!}</div><div class="nb2">Signature:</div><div class="sg">&nbsp;</div><div class="nb2">Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span class="v">{{ $d['userName'] }}</span></div><div class="nb2">Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span class="v"></span></div></td>
        <td class="b i"><div class="nb2">•&nbsp; Update&nbsp; ____________ (SRC)</div><div class="nb2 sg2">&nbsp;&nbsp;&nbsp;____________ (TECH)</div><div class="nb2">•&nbsp; Review&nbsp; ____________</div></td>
    </tr>
</table>

<div class="ea-foot"><span class="l">{!! $s1 !!} Select appropriate one</span><span class="r">Supersedes Rev 2.4 27 Aug 2009</span></div>
