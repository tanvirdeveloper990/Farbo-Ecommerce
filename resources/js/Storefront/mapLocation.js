export function mapLocation(value) {
    try {
        const url = new URL((value || '').trim());
        if (!['http:', 'https:'].includes(url.protocol)) return { link: null, embed: null };
        const host = url.hostname.toLowerCase();
        const googleMap = /(^|\.)(google\.[a-z.]+)$/.test(host) && url.pathname.startsWith('/maps');
        if (googleMap) {
            if (url.pathname.startsWith('/maps/embed')) return { link: url.href, embed: url.href };
            const coordinates = url.pathname.match(/!3d(-?[\d.]+)!4d(-?[\d.]+)/)
                || url.pathname.match(/@(-?[\d.]+),(-?[\d.]+)/);
            const query = url.searchParams.get('q') || url.searchParams.get('query')
                || url.searchParams.get('ll')
                || (coordinates ? `${coordinates[1]},${coordinates[2]}` : null)
                || (url.pathname.match(/\/place\/([^/]+)/)?.[1] ? decodeURIComponent(url.pathname.match(/\/place\/([^/]+)/)[1]).replaceAll('+', ' ') : null);
            return { link: url.href, embed: query ? `https://www.google.com/maps?q=${encodeURIComponent(query)}&output=embed` : null };
        }
        // Share links open the saved location directly; they cannot be embedded as an iframe.
        if (host === 'maps.app.goo.gl' || (host === 'goo.gl' && url.pathname.startsWith('/maps'))) return { link: url.href, embed: null };
        return { link: url.href, embed: null };
    } catch {
        return { link: null, embed: null };
    }
}
