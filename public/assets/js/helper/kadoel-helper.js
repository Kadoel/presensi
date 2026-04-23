/**
 * KadoelHelper
 * ============
 * Utility helper untuk manipulasi:
 * - waktu (HH:MM / HH:MM:SS)
 * - hari (Senin, Selasa, dst)
 *
 * Tujuan:
 * - membantu validasi frontend
 * - sinkron dengan logic backend
 * - reusable untuk form shift, jadwal, presensi, dll
 */
const KadoelHelper = {
    /**
     * Daftar nama hari dalam Bahasa Indonesia
     */
    hariIndo: [
        'minggu',
        'senin',
        'selasa',
        'rabu',
        'kamis',
        'jumat',
        'sabtu'
    ],

    /**
     * Daftar mapping nama hari ke nomor hari JavaScript
     * Minggu = 0, Senin = 1, ..., Sabtu = 6
     */
    hariMap: {
        minggu: 0,
        senin: 1,
        selasa: 2,
        rabu: 3,
        kamis: 4,
        jumat: 5,
        sabtu: 6
    },

    /**
     * Mengubah string waktu menjadi total detik
     *
     * @param {string} time - format "HH:MM" atau "HH:MM:SS"
     * @returns {number} total detik
     *
     * Contoh:
     * KadoelHelper.toSeconds("09:00") => 32400
     * KadoelHelper.toSeconds("09:00:30") => 32430
     */
    toSeconds(time) {
        if (!time || typeof time !== 'string') return 0;

        const a = time.split(':');

        if (a.length === 2) {
            return (+a[0] * 3600) + (+a[1] * 60);
        }

        return (+a[0] * 3600) + (+a[1] * 60) + (+a[2] || 0);
    },

    /**
     * Mengubah string waktu menjadi total menit
     *
     * @param {string} time - format "HH:MM" atau "HH:MM:SS"
     * @returns {number} total menit
     *
     * Contoh:
     * KadoelHelper.toMinutes("09:30") => 570
     */
    toMinutes(time) {
        return Math.floor(this.toSeconds(time) / 60);
    },

    /**
     * Mengubah format waktu menjadi HH:MM
     * Berguna untuk isi input edit/modal
     *
     * @param {string} time
     * @returns {string}
     *
     * Contoh:
     * KadoelHelper.toHHMM("09:00:00") => "09:00"
     * KadoelHelper.toHHMM("7:5:0") => "07:05"
     */
    toHHMM(time) {
        if (!time || typeof time !== 'string') return '';

        const a = time.split(':');
        const jam = (a[0] || '00').padStart(2, '0');
        const menit = (a[1] || '00').padStart(2, '0');

        return `${jam}:${menit}`;
    },

    /**
     * Mengubah total menit menjadi format HH:MM
     *
     * @param {number} totalMinutes
     * @returns {string}
     *
     * Contoh:
     * KadoelHelper.minutesToHHMM(570) => "09:30"
     */
    minutesToHHMM(totalMinutes) {
        if (isNaN(totalMinutes)) return '';

        const menitNormal = ((parseInt(totalMinutes, 10) % 1440) + 1440) % 1440;
        const jam = Math.floor(menitNormal / 60).toString().padStart(2, '0');
        const menit = (menitNormal % 60).toString().padStart(2, '0');

        return `${jam}:${menit}`;
    },

    /**
     * Menambahkan sejumlah menit ke waktu tertentu
     *
     * @param {string} time - format "HH:MM" atau "HH:MM:SS"
     * @param {number} minutes
     * @returns {string} format HH:MM
     *
     * Contoh:
     * KadoelHelper.addMinutes("09:00", 15) => "09:15"
     * KadoelHelper.addMinutes("23:50", 20) => "00:10"
     */
    addMinutes(time, minutes) {
        const totalMinutes = this.toMinutes(time) + parseInt(minutes || 0, 10);
        return this.minutesToHHMM(totalMinutes);
    },

    /**
     * Menghitung selisih menit antara dua waktu
     *
     * @param {string} start
     * @param {string} end
     * @returns {number}
     *
     * Contoh:
     * KadoelHelper.diffMinutes("09:00", "09:30") => 30
     * KadoelHelper.diffMinutes("10:00", "09:30") => -30
     */
    diffMinutes(start, end) {
        return this.toMinutes(end) - this.toMinutes(start);
    },

    /**
     * Mengecek apakah waktu A lebih kecil dari waktu B
     *
     * @param {string} time
     * @param {string} compare
     * @returns {boolean}
     */
    isBefore(time, compare) {
        return this.toSeconds(time) < this.toSeconds(compare);
    },

    /**
     * Mengecek apakah waktu A lebih kecil atau sama dengan waktu B
     *
     * @param {string} time
     * @param {string} compare
     * @returns {boolean}
     */
    isBeforeEqual(time, compare) {
        return this.toSeconds(time) <= this.toSeconds(compare);
    },

    /**
     * Mengecek apakah waktu A lebih besar dari waktu B
     *
     * @param {string} time
     * @param {string} compare
     * @returns {boolean}
     */
    isAfter(time, compare) {
        return this.toSeconds(time) > this.toSeconds(compare);
    },

    /**
     * Mengecek apakah waktu A lebih besar atau sama dengan waktu B
     *
     * @param {string} time
     * @param {string} compare
     * @returns {boolean}
     */
    isAfterEqual(time, compare) {
        return this.toSeconds(time) >= this.toSeconds(compare);
    },

    /**
     * Mengecek apakah dua waktu sama
     *
     * @param {string} time
     * @param {string} compare
     * @returns {boolean}
     */
    isEqual(time, compare) {
        return this.toSeconds(time) === this.toSeconds(compare);
    },

    /**
     * Mengecek apakah waktu berada di antara dua waktu
     *
     * @param {string} time
     * @param {string} start
     * @param {string} end
     * @param {boolean} inclusive - apakah termasuk batas
     * @returns {boolean}
     *
     * Contoh:
     * KadoelHelper.isBetween("07:30", "07:00", "08:00") => true
     */
    isBetween(time, start, end, inclusive = true) {
        const t = this.toSeconds(time);
        const s = this.toSeconds(start);
        const e = this.toSeconds(end);

        return inclusive
            ? (t >= s && t <= e)
            : (t > s && t < e);
    },

    /**
     * Mengecek apakah nilai waktu tidak kosong
     *
     * @param {string} time
     * @returns {boolean}
     */
    hasValue(time) {
        return !!(time && typeof time === 'string' && time.trim() !== '');
    },

    /**
     * Menormalkan nama hari menjadi huruf kecil dan trim
     *
     * @param {string} hari
     * @returns {string}
     *
     * Contoh:
     * KadoelHelper.normalizeDay(" Senin ") => "senin"
     */
    normalizeDay(hari) {
        if (!hari || typeof hari !== 'string') return '';
        return hari.trim().toLowerCase();
    },

    /**
     * Mengecek apakah nama hari valid
     *
     * @param {string} hari
     * @returns {boolean}
     *
     * Contoh:
     * KadoelHelper.isValidDay("Senin") => true
     * KadoelHelper.isValidDay("Libur") => false
     */
    isValidDay(hari) {
        return Object.prototype.hasOwnProperty.call(this.hariMap, this.normalizeDay(hari));
    },

    /**
     * Mengubah nama hari Indonesia menjadi nomor hari JavaScript
     * Minggu = 0, Senin = 1, ..., Sabtu = 6
     *
     * @param {string} hari
     * @returns {number|null}
     *
     * Contoh:
     * KadoelHelper.dayToNumber("Senin") => 1
     */
    dayToNumber(hari) {
        const day = this.normalizeDay(hari);
        return this.isValidDay(day) ? this.hariMap[day] : null;
    },

    /**
     * Mengubah nomor hari JavaScript menjadi nama hari Indonesia
     * Minggu = 0, Senin = 1, ..., Sabtu = 6
     *
     * @param {number} nomor
     * @returns {string}
     *
     * Contoh:
     * KadoelHelper.numberToDay(1) => "senin"
     */
    numberToDay(nomor) {
        const index = parseInt(nomor, 10);
        return this.hariIndo[index] || '';
    },

    /**
     * Mengambil nama hari Indonesia dari objek Date
     *
     * @param {Date} date
     * @returns {string}
     *
     * Contoh:
     * KadoelHelper.getDayName(new Date()) => "senin"
     */
    getDayName(date = new Date()) {
        if (!(date instanceof Date)) return '';
        return this.numberToDay(date.getDay());
    },

    /**
     * Mengecek apakah dua nama hari sama
     *
     * @param {string} dayA
     * @param {string} dayB
     * @returns {boolean}
     *
     * Contoh:
     * KadoelHelper.isSameDay("Senin", "senin") => true
     */
    isSameDay(dayA, dayB) {
        return this.normalizeDay(dayA) === this.normalizeDay(dayB);
    },

    /**
     * Mengecek apakah hari saat ini termasuk ke dalam daftar hari
     *
     * @param {string} currentDay
     * @param {Array<string>} days
     * @returns {boolean}
     *
     * Contoh:
     * KadoelHelper.inDays("senin", ["senin", "selasa"]) => true
     */
    inDays(currentDay, days = []) {
        const day = this.normalizeDay(currentDay);
        return days.map((item) => this.normalizeDay(item)).includes(day);
    },
    /**
 * Mengubah huruf pertama menjadi kapital
 *
 * @param {string} text
 * @returns {string}
 *
 * Contoh:
 * KadoelHelper.capitalize("senin") => "Senin"
 */
    capitalize(text) {
        if (!text || typeof text !== 'string') return '';
        return text.charAt(0).toUpperCase() + text.slice(1);
    },

    /**
     * Mengubah nama hari menjadi format kapital di awal
     *
     * @param {string} hari
     * @returns {string}
     *
     * Contoh:
     * KadoelHelper.capitalizeDay("senin") => "Senin"
     */
    capitalizeDay(hari) {
        const day = this.normalizeDay(hari);
        return this.capitalize(day);
    },

    /**
     * Mengambil nama hari dari Date dengan format kapital
     *
     * @param {Date} date
     * @returns {string}
     *
     * Contoh:
     * KadoelHelper.getDayNameFormatted(new Date()) => "Senin"
     */
    getDayNameFormatted(date = new Date()) {
        const day = this.getDayName(date);
        return this.capitalize(day);
    },

    /**
 * Mengubah tanggal format "YYYY-MM-DD" menjadi format Indonesia
 *
 * @param {string} tanggal - format "YYYY-MM-DD"
 * @returns {string}
 *
 * Contoh:
 * KadoelHelper.toTanggalIndonesia("1992-10-03") => "3 Oktober 1992"
 */
    toTanggalIndonesia(tanggal) {
        if (!tanggal || typeof tanggal !== 'string') return '';

        const bulanArray = [
            '', // index 0 dikosongkan supaya index bulan sesuai (1-12)
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];

        const splitStr = tanggal.split('-');

        if (splitStr.length !== 3) return '';

        const tahun = splitStr[0];
        const bulan = parseInt(splitStr[1], 10);
        const hari = parseInt(splitStr[2], 10);

        if (!bulanArray[bulan]) return '';

        return `${hari} ${bulanArray[bulan]} ${tahun}`;
    },

    /**
 * Mengekstrak nilai dari array object menjadi array biasa
 *
 * @param {Array<Object>} arr
 * @param {Array<string>} keys - field yang mau diambil
 * @returns {Array}
 *
 * Contoh:
 * KadoelHelper.ekstrakDataArray(data, ['id', 'tanggal'])
 * => [1, "2024-01-01", 2, "2024-01-02"]
 */
    ekstrakDataArray(arr = [], keys = []) {
        if (!Array.isArray(arr)) return [];

        let result = [];

        arr.forEach(item => {
            keys.forEach(key => {
                if (item && Object.prototype.hasOwnProperty.call(item, key)) {
                    result.push(item[key]);
                }
            });
        });

        return result;
    },

    /**
     * Mengecek apakah suatu nilai ada di dalam array object
     *
     * @param {Array<Object>} arr
     * @param {*} param - nilai yang dicari
     * @param {Array<string>} keys - field yang dicek
     * @returns {boolean}
     *
     * Contoh:
     * KadoelHelper.checkArrayExist(data, 1, ['id'])
     * KadoelHelper.checkArrayExist(data, '2024-01-01', ['tanggal'])
     */
    checkArrayExist(arr = [], param, keys = []) {
        const sources = this.ekstrakDataArray(arr, keys);
        return sources.includes(param);
    },

    /**
     * Mengubah setiap kata menjadi kapital di awal
     *
     * @param {string} text
     * @returns {string}
     *
     * Contoh:
     * KadoelHelper.toCapitalizeEachWord("hello world")
     * => "Hello World"
     */
    toCapitalizeEachWord(text) {
        if (!text || typeof text !== 'string') return '';

        return text
            .toLowerCase()
            .split(/[\s\-_]+/) // 🔥 split: spasi, -, _
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }
};

window.KadoelHelper = KadoelHelper;