<form method="POST" action="#" autocomplete="off" id="form-surat-cuti">
    @csrf
    @php
        $defaults = $suratCutiDefaults ?? [];
    @endphp
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nama</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', $defaults['nama'] ?? '') }}" placeholder="Masukkan nama">
        </div>

        <div class="col-md-6">
            <label class="form-label">Jabatan</label>
            <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $defaults['jabatan'] ?? '') }}" placeholder="Masukkan jabatan">
        </div>

        <div class="col-md-6">
            <label class="form-label">Unit Kerja</label>
            <input type="text" class="form-control" value="UPTD RSUD dr. Zubir Mahmud Idi Kabupaten Aceh Timur" readonly>
        </div>

        <div class="col-md-6">
            <label class="form-label">NIP</label>
            <input type="text" name="nip" class="form-control" value="{{ old('nip', $defaults['nip'] ?? '') }}" placeholder="Masukkan NIP">
        </div>

        <div class="col-md-6">
            <label class="form-label">Masa Kerja</label>
            <div class="row g-2">
                <div class="col-6">
                    <div class="input-group">
                        <input type="number" min="0" name="tahun" class="form-control" value="{{ old('tahun', $defaults['tahun'] ?? '') }}" placeholder="0">
                        <span class="input-group-text">Tahun</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="input-group">
                        <input type="number" min="0" max="11" name="bulan" class="form-control" value="{{ old('bulan', $defaults['bulan'] ?? '') }}" placeholder="0">
                        <span class="input-group-text">Bulan</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">Jenis Cuti</label>
            <select name="jenis_cuti" class="form-select">
                <option value="">Pilih Jenis Cuti</option>
                <option value="Cuti Tahunan" @selected(old('jenis_cuti', $defaults['jenis_cuti'] ?? '') === 'Cuti Tahunan')>Cuti Tahunan</option>
                <option value="Cuti Besar" @selected(old('jenis_cuti', $defaults['jenis_cuti'] ?? '') === 'Cuti Besar')>Cuti Besar</option>
                <option value="Cuti Sakit" @selected(old('jenis_cuti', $defaults['jenis_cuti'] ?? '') === 'Cuti Sakit')>Cuti Sakit</option>
                <option value="Cuti Melahirkan" @selected(old('jenis_cuti', $defaults['jenis_cuti'] ?? '') === 'Cuti Melahirkan')>Cuti Melahirkan</option>
                <option value="Cuti Karena Alasan Penting" @selected(old('jenis_cuti', $defaults['jenis_cuti'] ?? '') === 'Cuti Karena Alasan Penting')>Cuti Karena Alasan Penting</option>
                <option value="Cuti di Luar Tanggungan Negara" @selected(old('jenis_cuti', $defaults['jenis_cuti'] ?? '') === 'Cuti di Luar Tanggungan Negara')>Cuti di Luar Tanggungan Negara</option>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Alasan Cuti</label>
            <textarea name="alasan" class="form-control" rows="3" placeholder="Masukkan alasan cuti">{{ old('alasan', $defaults['alasan'] ?? '') }}</textarea>
        </div>

        <div class="col-md-4">
            <label class="form-label">Selama (Hari)</label>
            <input type="number" min="1" name="hari" id="hari" class="form-control" value="{{ old('hari', $defaults['hari'] ?? '') }}" placeholder="Masukkan jumlah hari">
        </div>

        <div class="col-md-4">
            <label class="form-label">Mulai Tanggal</label>
            <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" value="{{ old('tgl_mulai', $defaults['tgl_mulai'] ?? '') }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="selesai" id="selesai" class="form-control" value="{{ old('selesai', $defaults['selesai'] ?? '') }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">Telp</label>
            <input type="text" name="telp" class="form-control" value="{{ old('telp', $defaults['telp'] ?? '-') }}" readonly>
        </div>

        <div class="col-12">
            <label class="form-label">Alamat Selama Menjalankan Cuti</label>
            <textarea name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat selama menjalankan cuti">{{ old('alamat', $defaults['alamat'] ?? '') }}</textarea>
        </div>

        <div class="col-md-4">
            <label class="form-label">Tanggal Surat</label>
            <input type="date" name="tgl_surat_input" id="tgl_surat_input" class="form-control" value="{{ old('tgl_surat_input', $defaults['tgl_surat'] ?? '') }}">
            <input type="hidden" name="tgl_surat" id="tgl_surat" value="{{ old('tgl_surat') }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">Bulan Surat</label>
            <input type="text" name="bulan_surat" id="bulan_surat" class="form-control" value="{{ old('bulan_surat') }}" placeholder="Otomatis dari tanggal surat" readonly>
        </div>

        <div class="col-md-4">
            <label class="form-label">Tahun Surat</label>
            <input type="text" name="tahun_surat" id="tahun_surat" class="form-control" value="{{ old('tahun_surat') }}" placeholder="Otomatis dari tanggal surat" readonly>
        </div>

        <div class="col-md-4">
            <label class="form-label">Atasan Langsung</label>
            <input type="text" name="atasan_langsung" class="form-control" value="{{ old('atasan_langsung', $defaults['atasan_langsung'] ?? '') }}" placeholder="Masukkan nama atasan langsung">
        </div>

        <div class="col-md-4">
            <label class="form-label">Jabatan Atasan Langsung</label>
            <input type="text" name="jbtn_atasan_langsung" class="form-control" value="{{ old('jbtn_atasan_langsung', $defaults['jbtn_atasan_langsung'] ?? '') }}" placeholder="Masukkan jabatan atasan langsung">
        </div>

        <div class="col-md-4">
            <label class="form-label">NIP Atasan Langsung</label>
            <input type="text" name="nip_atasan_langsung" class="form-control" value="{{ old('nip_atasan_langsung', $defaults['nip_atasan_langsung'] ?? '') }}" placeholder="Masukkan NIP atasan langsung">
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">
                <i data-feather="printer" class="me-1"></i> Cetak Surat Cuti
            </button>
        </div>
    </div>
</form>

<script>
    (function () {
        const form = document.getElementById('form-surat-cuti');
        if (!form) return;

        const lamaHariInput = form.querySelector('#hari');
        const mulaiTanggalInput = form.querySelector('#tgl_mulai');
        const sampaiTanggalInput = form.querySelector('#selesai');
        const tanggalSuratInput = form.querySelector('#tgl_surat_input');
        const tanggalSuratHidden = form.querySelector('#tgl_surat');
        const bulanSuratInput = form.querySelector('#bulan_surat');
        const tahunSuratInput = form.querySelector('#tahun_surat');
        if (!lamaHariInput || !mulaiTanggalInput || !sampaiTanggalInput) return;
        const holidayCache = new Map(); // year -> Set('YYYY-MM-DD')
        const namaBulanIndonesia = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        function isWeekend(date) {
            const day = date.getDay(); // 0=minggu, 6=sabtu
            return day === 0 || day === 6;
        }

        function toIsoDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function parseDate(value) {
            if (!value) return null;
            const [year, month, day] = value.split('-').map(Number);
            if (!year || !month || !day) return null;
            return new Date(year, month - 1, day);
        }

        async function loadNationalHolidays(year) {
            if (holidayCache.has(year)) return holidayCache.get(year);

            try {
                const res = await fetch(`https://date.nager.at/api/v3/PublicHolidays/${year}/ID`);
                if (!res.ok) throw new Error('Failed to fetch holidays');
                const data = await res.json();
                const holidays = new Set(
                    Array.isArray(data)
                        ? data.map(item => item?.date).filter(Boolean)
                        : []
                );
                holidayCache.set(year, holidays);
                return holidays;
            } catch (error) {
                console.error('Gagal memuat hari libur nasional:', error);
                const empty = new Set();
                holidayCache.set(year, empty);
                return empty;
            }
        }

        async function isNationalHoliday(date) {
            const year = date.getFullYear();
            const holidays = await loadNationalHolidays(year);
            return holidays.has(toIsoDate(date));
        }

        async function calculateEndDate() {
            const lamaHari = Number(lamaHariInput.value);
            const mulaiDate = parseDate(mulaiTanggalInput.value);

            if (!lamaHari || lamaHari < 1 || !mulaiDate) {
                return;
            }

            sampaiTanggalInput.value = '';
            sampaiTanggalInput.placeholder = 'Menghitung...';

            let current = new Date(mulaiDate);
            let counted = 0;

            while (counted < lamaHari) {
                const isHoliday = await isNationalHoliday(current);
                if (!isWeekend(current) && !isHoliday) {
                    counted++;
                }

                if (counted < lamaHari) {
                    current.setDate(current.getDate() + 1);
                }
            }

            sampaiTanggalInput.value = toIsoDate(current);
            sampaiTanggalInput.placeholder = '';
        }

        function syncTanggalSuratParts() {
            if (!tanggalSuratInput || !tanggalSuratHidden || !bulanSuratInput || !tahunSuratInput) {
                return;
            }

            const tanggal = parseDate(tanggalSuratInput.value);
            if (!tanggal) {
                tanggalSuratHidden.value = '';
                bulanSuratInput.value = '';
                tahunSuratInput.value = '';
                return;
            }

            tanggalSuratHidden.value = String(tanggal.getDate());
            bulanSuratInput.value = namaBulanIndonesia[tanggal.getMonth()] ?? '';
            tahunSuratInput.value = String(tanggal.getFullYear());
        }

        lamaHariInput.addEventListener('input', calculateEndDate);
        mulaiTanggalInput.addEventListener('change', calculateEndDate);
        if (tanggalSuratInput) {
            tanggalSuratInput.addEventListener('change', syncTanggalSuratParts);
        }

        if (lamaHariInput.value && mulaiTanggalInput.value && !sampaiTanggalInput.value) {
            calculateEndDate();
        }
        syncTanggalSuratParts();
    })();
</script>
