// Times of a site are shown in the time zone of the site's LOCATION, not the
// viewer's: a site in Kalimantan Selatan is on WITA, one in Kalimantan Tengah
// on WIB, one in Papua on WIT — whoever is looking, from wherever.
//
// The location is the free text typed in Manage Sites ("Kalimantan Selatan",
// "KALIMANTAN TENGAH", ...). It is matched against the Indonesian provinces
// (and a few well-known cities and abbreviations). A location that matches
// nothing falls back to the viewer's own time zone, as before, and shows no
// zone label — we never guess.
const WIB = 'Asia/Jakarta';
const WITA = 'Asia/Makassar';
const WIT = 'Asia/Jayapura';

const ZONE_LABEL = { [WIB]: 'WIB', [WITA]: 'WITA', [WIT]: 'WIT' };

// keyword (lowercase, letters and spaces only) -> zone. Matched as whole words.
// Kalimantan is deliberately split by province: it spans WIB and WITA.
const LOCATION_ZONES = [
    // WIB (UTC+7): Sumatera, Jawa, Kalimantan Barat & Tengah
    ...['aceh', 'sumatera', 'sumatra', 'riau', 'kepulauan riau', 'kepri', 'jambi', 'bengkulu', 'lampung', 'bangka', 'belitung',
        'sumut', 'sumbar', 'sumsel', 'babel', 'jakarta', 'banten', 'jawa', 'jabar', 'jateng', 'jatim', 'yogyakarta', 'diy',
        'kalimantan barat', 'kalbar', 'kalimantan tengah', 'kalteng',
        'medan', 'palembang', 'batam', 'pekanbaru', 'bandung', 'semarang', 'surabaya', 'pontianak', 'palangkaraya', 'palangka raya',
        'sampit', 'pangkalan bun', 'kotawaringin'].map((key) => [key, WIB]),
    // WITA (UTC+8): Bali, Nusa Tenggara, Sulawesi, Kalimantan Selatan / Timur / Utara
    ...['bali', 'denpasar', 'nusa tenggara', 'ntb', 'ntt', 'sulawesi', 'sulut', 'sulteng', 'sulsel', 'sultra', 'sulbar', 'gorontalo',
        'kalimantan selatan', 'kalsel', 'kalimantan timur', 'kaltim', 'kalimantan utara', 'kaltara',
        'makassar', 'manado', 'kendari', 'palu', 'mataram', 'kupang', 'banjarmasin', 'banjarbaru', 'tabalong', 'tanjung', 'barito',
        'samarinda', 'balikpapan', 'bontang', 'tarakan', 'berau', 'kutai', 'paser', 'tanah bumbu', 'kotabaru'].map((key) => [key, WITA]),
    // WIT (UTC+9): Maluku, Papua
    ...['maluku', 'ambon', 'ternate', 'papua', 'jayapura', 'sorong', 'manokwari', 'merauke', 'timika'].map((key) => [key, WIT]),
].sort((a, b) => b[0].length - a[0].length); // the most specific keyword wins

function normalise(text) {
    return ` ${String(text ?? '').toLowerCase().replace(/[^a-z]+/g, ' ').trim()} `;
}

/** IANA zone for a site location, or null when the location is not recognised. */
export function timeZoneForLocation(location) {
    const haystack = normalise(location);
    if (haystack.trim() === '') return null;

    return LOCATION_ZONES.find(([keyword]) => haystack.includes(` ${keyword} `))?.[1] ?? null;
}

/** 'WIB' | 'WITA' | 'WIT', or '' when the location is not recognised. */
export function zoneLabelForLocation(location) {
    return ZONE_LABEL[timeZoneForLocation(location)] ?? '';
}

// A site's login log reports the clock time of the login in UTC but labels it
// with the site's own offset (e.g. '2026-10-16T02:50:44+08:00' for a login at
// 10:50 WITA). Reading the offset literally shows the UTC clock time as if it
// were local, 7-9 hours early. So for that log only, the digits are taken as
// UTC and the offset is ignored. Set this to false once the sites send correct
// offsets.
export const SITE_LOG_CLOCK_IS_UTC = true;

function parse(value, { digitsAreUtc }) {
    if (!value) return null;

    if (digitsAreUtc) {
        const match = /^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2}(?::\d{2})?)/.exec(String(value));
        if (match) {
            const date = new Date(`${match[1]}T${match[2].length === 5 ? `${match[2]}:00` : match[2]}Z`);
            return Number.isNaN(date.getTime()) ? null : date;
        }
    }

    const date = new Date(value);

    return Number.isNaN(date.getTime()) ? null : date;
}

/**
 * '16 Oct 2026, 10:50 WITA' — the moment `value` in the time zone of `location`.
 * An unrecognised location shows the viewer's own time, without a zone label.
 */
export function formatSiteTime(value, location, { digitsAreUtc = false } = {}) {
    const date = parse(value, { digitsAreUtc });
    if (!date) return '—';

    const timeZone = timeZoneForLocation(location);
    const text = date.toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
        ...(timeZone ? { timeZone } : {}),
    });

    return timeZone ? `${text} ${ZONE_LABEL[timeZone]}` : text;
}
