<form method="POST" action="#" autocomplete="off" id="form-surat-cuti">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nama</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', auth()->user()->name ?? '') }}" placeholder="Masukkan nama">
        </div>

        <div class="col-md-6">
            <label class="form-label">Jabatan</label>
            <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan') }}" placeholder="Masukkan jabatan">
        </div>

        <div class="col-md-6">
            <label class="form-label">Unit Kerja</label>
            <input type="text" class="form-control" value="UPTD RSUD dr. Zubir Mahmud Idi Kabupaten Aceh Timur" readonly>
        </div>

        <div class="col-md-6">
            <label class="form-label">NIP</label>
            <input type="text" name="nip" class="form-control" value="{{ old('nip') }}" placeholder="Masukkan NIP">
        </div>

        <div class="col-md-6">
            <label class="form-label">Masa Kerja</label>
            <div class="row g-2">
                <div class="col-6">
                    <div class="input-group">
                        <input type="number" min="0" name="masa_kerja_tahun" class="form-control" value="{{ old('masa_kerja_tahun') }}" placeholder="0">
                        <span class="input-group-text">Tahun</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="input-group">
                        <input type="number" min="0" max="11" name="masa_kerja_bulan" class="form-control" value="{{ old('masa_kerja_bulan') }}" placeholder="0">
                        <span class="input-group-text">Bulan</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">Jenis Cuti</label>
            <select name="jenis_cuti" class="form-select">
                <option value="">Pilih Jenis Cuti</option>
                <option value="Cuti Tahunan" @selected(old('jenis_cuti') === 'Cuti Tahunan')>Cuti Tahunan</option>
                <option value="Cuti Besar" @selected(old('jenis_cuti') === 'Cuti Besar')>Cuti Besar</option>
                <option value="Cuti Sakit" @selected(old('jenis_cuti') === 'Cuti Sakit')>Cuti Sakit</option>
                <option value="Cuti Melahirkan" @selected(old('jenis_cuti') === 'Cuti Melahirkan')>Cuti Melahirkan</option>
                <option value="Cuti Karena Alasan Penting" @selected(old('jenis_cuti') === 'Cuti Karena Alasan Penting')>Cuti Karena Alasan Penting</option>
                <option value="Cuti di Luar Tanggungan Negara" @selected(old('jenis_cuti') === 'Cuti di Luar Tanggungan Negara')>Cuti di Luar Tanggungan Negara</option>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Alasan Cuti</label>
            <textarea name="alasan_cuti" class="form-control" rows="3" placeholder="Masukkan alasan cuti">{{ old('alasan_cuti') }}</textarea>
        </div>

        <div class="col-md-4">
            <label class="form-label">Selama (Hari)</label>
            <input type="number" min="1" name="lama_hari" id="lama_hari" class="form-control" value="{{ old('lama_hari') }}" placeholder="Masukkan jumlah hari">
        </div>

        <div class="col-md-4">
            <label class="form-label">Mulai Tanggal</label>
            <input type="date" name="mulai_tanggal" id="mulai_tanggal" class="form-control" value="{{ old('mulai_tanggal') }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="sampai_tanggal" id="sampai_tanggal" class="form-control" value="{{ old('sampai_tanggal') }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">Telp</label>
            @php
                $rawPhone = auth()->user()->phone ?? '';
                $displayPhone = preg_replace('/^62/', '0', $rawPhone);
            @endphp
            <input type="text" class="form-control" value="{{ $displayPhone !== '' ? $displayPhone : '-' }}" readonly>
        </div>

        <div class="col-12">
            <label class="form-label">Alamat Selama Menjalankan Cuti</label>
            <textarea name="alamat_cuti" class="form-control" rows="3" placeholder="Masukkan alamat selama menjalankan cuti">{{ old('alamat_cuti') }}</textarea>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Ajukan Pengajuan
            </button>
        </div>
    </div>
</form>

<script>
    (function () {
        const form = document.getElementById('form-surat-cuti');
        if (!form) return;

        const lamaHariInput = form.querySelector('#lama_hari');
        const mulaiTanggalInput = form.querySelector('#mulai_tanggal');
        const sampaiTanggalInput = form.querySelector('#sampai_tanggal');
        if (!lamaHariInput || !mulaiTanggalInput || !sampaiTanggalInput) return;
        const holidayCache = new Map(); // year -> Set('YYYY-MM-DD')

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

        lamaHariInput.addEventListener('input', calculateEndDate);
        mulaiTanggalInput.addEventListener('change', calculateEndDate);

        if (lamaHariInput.value && mulaiTanggalInput.value && !sampaiTanggalInput.value) {
            calculateEndDate();
        }
    })();
</script>
