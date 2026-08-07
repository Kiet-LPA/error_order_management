/**
 * Reverse geocode lat/lng → tên địa điểm (vd: "Bình Đức, An Giang")
 * API: BigDataCloud (free client, no key)
 */
(function (global) {
  function formatLocationName(data) {
    if (!data || typeof data !== 'object') return null;
    const locality = data.locality || data.city || '';
    const province = data.principalSubdivision || '';
    const parts = [locality, province].filter(Boolean);
    return [...new Set(parts)].join(', ') || null;
  }

  async function resolveLocationName(lat, lng) {
    if (lat === null || lat === undefined || lng === null || lng === undefined) {
      return null;
    }
    const latN = Number(lat);
    const lngN = Number(lng);
    if (Number.isNaN(latN) || Number.isNaN(lngN)) return null;

    const url =
      'https://api.bigdatacloud.net/data/reverse-geocode-client' +
      `?latitude=${encodeURIComponent(latN)}&longitude=${encodeURIComponent(lngN)}` +
      '&localityLanguage=vi';

    const res = await fetch(url);
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();
    return formatLocationName(data);
  }

  /**
   * Fill elements having data-lat / data-lng (and optional data-name already set).
   * Element text becomes: "Bình Đức, An Giang" (keeps inner HTML for empty).
   */
  async function fillLocationElements(root) {
    const scope = root || document;
    const cells = Array.from(scope.querySelectorAll('[data-lat][data-lng]'));
    for (const el of cells) {
      if (el.dataset.name) {
        if (!el.textContent.trim()) el.textContent = el.dataset.name;
        continue;
      }
      const lat = el.dataset.lat;
      const lng = el.dataset.lng;
      if (!lat || !lng) continue;
      try {
        const name = await resolveLocationName(lat, lng);
        el.textContent = name || `${lat}, ${lng}`;
        if (name) el.dataset.name = name;
      } catch (e) {
        el.textContent = `${lat}, ${lng}`;
      }
    }
  }

  global.LocationName = {
    format: formatLocationName,
    resolve: resolveLocationName,
    fillElements: fillLocationElements,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      fillLocationElements(document);
    });
  } else {
    fillLocationElements(document);
  }
})(window);
