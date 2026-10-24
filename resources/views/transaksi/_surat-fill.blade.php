<script>
    // Memanjangkan ruang tanda tangan ke bawah sampai tepat di atas teks kecil di dasar halaman
    // (hanya tampilan layar / tombol Cetak; PDF dihitung terpisah di controller).
    window.eaFillSignature = function (page) {
        try {
            var sgs = page.querySelectorAll('.sg');
            var sgcs = page.querySelectorAll('.sgc');                     // jarak antar baris kolom CMDB-Only
            var foot = page.querySelector('.ea-foot');
            if (!sgs.length || !foot) return;

            sgs.forEach(function (el) { el.style.height = ''; });          // mulai dari tinggi asli
            sgcs.forEach(function (el) { el.style.height = ''; });
            var zoom = page.style.zoom;
            page.style.zoom = 1;                                           // ukur pada skala 100%

            var cs = getComputedStyle(page);
            var before = page.getBoundingClientRect();
            var limit = before.bottom - (parseFloat(cs.paddingBottom) || 0) - (parseFloat(cs.borderBottomWidth) || 0);
            var free = limit - foot.getBoundingClientRect().bottom;

            if (free > 4 && free < 700) {
                var base = sgs[0].getBoundingClientRect().height;
                var tinggi = base + free - 2;
                var baris = parseFloat(cs.lineHeight) || 14.2;                 // tinggi satu baris teks
                sgs.forEach(function (el) { el.style.height = tinggi + 'px'; });
                // kolom CMDB: tiga baris tersebar rata atas-bawah mengikuti tinggi kolom kiri (4 baris + ruang tanda tangan)
                sgcs.forEach(function (el) { el.style.height = Math.max(0, (tinggi + baris) / 2 - 0.5) + 'px'; });

                // pengaman: jika halaman jadi lebih tinggi dari semula, batalkan
                if (page.getBoundingClientRect().height > before.height + 1) {
                    sgs.forEach(function (el) { el.style.height = ''; });
                    sgcs.forEach(function (el) { el.style.height = ''; });
                }
            }
            page.style.zoom = zoom;
        } catch (e) { /* abaikan: surat tetap tampil dengan tinggi standar */ }
    };
</script>
