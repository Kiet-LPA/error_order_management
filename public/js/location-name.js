/**
 * Reverse geocode → tên địa điểm (nhanh: cache + parallel + timeout)
 */
(function (global) {
  var CACHE_PREFIX = 'locname:v1:';
  var CACHE_TTL_MS = 30 * 24 * 60 * 60 * 1000; // 30 ngày
  var FETCH_TIMEOUT_MS = 2500;
  var inFlight = {};

  function cacheKey(lat, lng) {
    // ~11m precision — đủ cho tên phường/xã
    return Number(lat).toFixed(4) + ',' + Number(lng).toFixed(4);
  }

  function readCache(key) {
    try {
      var raw = localStorage.getItem(CACHE_PREFIX + key);
      if (!raw) return null;
      var obj = JSON.parse(raw);
      if (!obj || !obj.t || Date.now() - obj.t > CACHE_TTL_MS) {
        localStorage.removeItem(CACHE_PREFIX + key);
        return null;
      }
      return obj.n || null;
    } catch (e) {
      return null;
    }
  }

  function writeCache(key, name) {
    if (!name) return;
    try {
      localStorage.setItem(
        CACHE_PREFIX + key,
        JSON.stringify({ n: name, t: Date.now() })
      );
    } catch (e) {
      // quota full — bỏ qua
    }
  }

  function formatLocationName(data) {
    if (!data || typeof data !== 'object') return null;
    var locality = data.locality || data.city || '';
    var province = data.principalSubdivision || '';
    var parts = [locality, province].filter(Boolean);
    var seen = {};
    var uniq = [];
    for (var i = 0; i < parts.length; i++) {
      if (!seen[parts[i]]) {
        seen[parts[i]] = 1;
        uniq.push(parts[i]);
      }
    }
    return uniq.join(', ') || null;
  }

  function fallbackLabel(lat, lng) {
    return Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5);
  }

  async function fetchWithTimeout(url, ms) {
    var controller = new AbortController();
    var timer = setTimeout(function () {
      controller.abort();
    }, ms);
    try {
      return await fetch(url, { signal: controller.signal });
    } finally {
      clearTimeout(timer);
    }
  }

  async function resolveLocationName(lat, lng) {
    if (lat === null || lat === undefined || lng === null || lng === undefined) {
      return null;
    }
    var latN = Number(lat);
    var lngN = Number(lng);
    if (Number.isNaN(latN) || Number.isNaN(lngN)) return null;

    var key = cacheKey(latN, lngN);
    var cached = readCache(key);
    if (cached) return cached;

    // Deduplicate concurrent requests for same coords
    if (inFlight[key]) {
      return inFlight[key];
    }

    inFlight[key] = (async function () {
      try {
        // BigDataCloud trước (nhanh, CORS OK)
        var url =
          'https://api.bigdatacloud.net/data/reverse-geocode-client' +
          '?latitude=' +
          encodeURIComponent(latN) +
          '&longitude=' +
          encodeURIComponent(lngN) +
          '&localityLanguage=vi';

        var res = await fetchWithTimeout(url, FETCH_TIMEOUT_MS);
        if (res.ok) {
          var data = await res.json();
          var name = formatLocationName(data);
          if (name) {
            writeCache(key, name);
            return name;
          }
        }
      } catch (e) {
        // timeout / network
      }
      return null;
    })();

    try {
      return await inFlight[key];
    } finally {
      delete inFlight[key];
    }
  }

  function setElName(el, text) {
    if (!el) return;
    el.textContent = text;
    el.dataset.name = text;
  }

  /**
   * Fill all [data-lat][data-lng] in parallel (không chờ từng cái).
   */
  async function fillLocationElements(root) {
    var scope = root || document;
    var cells = Array.prototype.slice.call(
      scope.querySelectorAll('[data-lat][data-lng]')
    );
    if (!cells.length) return;

    await Promise.all(
      cells.map(async function (el) {
        if (el.dataset.name && el.dataset.name !== 'Đang tải vị trí...' && el.dataset.name !== 'Đang tải tên vị trí...') {
          if (!el.textContent.trim() || el.textContent.indexOf('Đang tải') === 0) {
            el.textContent = el.dataset.name;
          }
          return;
        }

        var lat = el.dataset.lat;
        var lng = el.dataset.lng;
        if (!lat || !lng) return;

        // Hiển thị cache ngay nếu có (không chờ network)
        var key = cacheKey(lat, lng);
        var cached = readCache(key);
        if (cached) {
          setElName(el, cached);
          return;
        }

        try {
          var name = await resolveLocationName(lat, lng);
          setElName(el, name || fallbackLabel(lat, lng));
        } catch (e) {
          setElName(el, fallbackLabel(lat, lng));
        }
      })
    );
  }

  global.LocationName = {
    format: formatLocationName,
    resolve: resolveLocationName,
    fillElements: fillLocationElements,
    fallback: fallbackLabel,
  };

  function boot() {
    fillLocationElements(document);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})(window);
