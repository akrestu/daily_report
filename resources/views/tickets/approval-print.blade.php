<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formulir Persetujuan {{ $ticket->ticket_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000; background: #e9ecef; margin: 0; }
        .page { width: 210mm; min-height: 297mm; margin: 16px auto; background: #fff; padding: 18mm 16mm; }
        .header { display: flex; align-items: center; gap: 12px; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 14px; }
        .header img { height: 48px; }
        .header h1 { font-size: 15px; margin: 0; text-transform: uppercase; }
        .header .sub { font-size: 11px; color: #333; }
        .meta { text-align: right; margin-left: auto; font-size: 11px; }
        h2 { font-size: 12px; text-transform: uppercase; background: #f1f3f5; padding: 4px 6px; margin: 14px 0 6px; border: 1px solid #ccc; }
        table.info { width: 100%; border-collapse: collapse; }
        table.info td { padding: 4px 6px; border: 1px solid #ccc; vertical-align: top; }
        table.info td.label { width: 32%; background: #fafafa; }
        .desc { white-space: pre-wrap; min-height: 60px; }
        .statement { margin: 14px 0; line-height: 1.6; }
        .decision span { display: inline-block; margin-right: 28px; }
        .box { display: inline-block; width: 14px; height: 14px; border: 1px solid #000; vertical-align: middle; margin-right: 6px; }
        table.sign { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.sign td { width: 50%; border: 1px solid #000; text-align: center; padding: 6px; vertical-align: top; }
        table.sign .space { height: 80px; }
        .footer { margin-top: 18px; font-size: 10px; color: #333; border-top: 1px dashed #999; padding-top: 6px; }
        .toolbar { text-align: center; margin: 12px 0 0; }
        .toolbar button { font-size: 14px; padding: 8px 20px; cursor: pointer; }
        @media screen and (max-width: 820px) {
            .page { width: auto; min-height: auto; margin: 8px; padding: 16px; }
        }
        @media print {
            body { background: #fff; }
            .page { margin: 0; width: auto; min-height: auto; padding: 0; }
            .toolbar { display: none; }
            @page { size: A4; margin: 15mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Cetak Formulir</button></div>

    <div class="page">
        <div class="header">
            <img src="{{ asset('Sigap.png') }}" alt="">
            <div>
                <h1>Formulir Persetujuan Permintaan Layanan IT</h1>
                <div class="sub">PT. WBK &mdash; Departemen IT</div>
            </div>
            <div class="meta">
                No. Tiket: <strong>{{ $ticket->ticket_number }}</strong><br>
                Tanggal: {{ $ticket->created_at->translatedFormat('d F Y') }}
            </div>
        </div>

        <h2>Data Pemohon</h2>
        <table class="info">
            <tr><td class="label">Nama</td><td>{{ $ticket->requester_name }}</td></tr>
            <tr><td class="label">NIK / ID</td><td>{{ $ticket->requester_employee_id ?? '-' }}</td></tr>
            <tr><td class="label">Departemen</td><td>{{ $ticket->requester_department }}</td></tr>
        </table>

        <h2>Detail Permintaan</h2>
        <table class="info">
            <tr><td class="label">Kategori</td><td>{{ $ticket->category->name ?? '-' }}</td></tr>
            <tr><td class="label">Jenis</td><td>{{ $ticket->type_label }}</td></tr>
            <tr><td class="label">Judul</td><td>{{ $ticket->title }}</td></tr>
            <tr><td class="label">Deskripsi / Alasan Kebutuhan</td><td class="desc">{{ $ticket->description }}</td></tr>
        </table>

        <h2>Persetujuan Atasan Langsung</h2>
        <table class="info">
            <tr><td class="label">Nama Atasan</td><td>{{ $ticket->supervisor->name ?? '-' }}</td></tr>
            <tr><td class="label">NIK</td><td>{{ $ticket->supervisor->nik ?? '-' }}</td></tr>
            <tr><td class="label">Jabatan</td><td>{{ $ticket->supervisor->position ?? '-' }}</td></tr>
        </table>

        <div class="statement">
            Saya selaku atasan langsung pemohon menyatakan bahwa permintaan di atas:
            <div class="decision" style="margin-top:6px">
                <span><span class="box"></span>DISETUJUI</span>
                <span><span class="box"></span>TIDAK DISETUJUI</span>
            </div>
            Catatan: ......................................................................................................................................................
        </div>

        <table class="sign">
            <tr><td>Pemohon</td><td>Atasan Langsung</td></tr>
            <tr><td class="space"></td><td class="space"></td></tr>
            <tr>
                <td>( {{ $ticket->requester_name }} )<br>Tanggal: ....................</td>
                <td>( {{ $ticket->supervisor->name ?? '..............................' }} )<br>Tanggal: ....................</td>
            </tr>
        </table>

        <div class="footer">
            Setelah ditandatangani, foto atau scan formulir ini lalu unggah melalui halaman tiket
            <strong>{{ $ticket->ticket_number }}</strong> (menu Tiket Saya, atau Cek Status Tiket di {{ route('tickets.public.track') }}).
            Tiket akan dikerjakan setelah bukti persetujuan diverifikasi tim IT.
        </div>
    </div>
</body>
</html>
