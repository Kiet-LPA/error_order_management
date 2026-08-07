/**
 * Reverse geocode → tên địa điểm.
 * Ưu tiên API cùng domain (server có cache + BigDataCloud + Nominatim),
 * fallback BigDataCloud từ browser nếu server lỗi.
 */
(function (global) {
  var CACHE_PREFIX = 'locname:v2:';
  var CACHE_TTL_MS = 30 * 24 * 60 * 60 * 1000; // 30 ngày
  var FETCH_TIMEOUT_MS = 8000;
  var inFlight = {};

  function cacheKey(lat, lng) {
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
      // quota full
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

  function apiUrl(lat, lng) {
    return (
      '/api/location-name?lat=' +
      encodeURIComponent(lat) +
      '&lng=' +
      encodeURIComponent(lng)
    );
  }

  async function fetchWithTimeout(url, ms) {
    var controller = new AbortController();
    var timer = setTimeout(function () {
      controller.abort();
    }, ms);
    try {
      return await fetch(url, {
        signal: controller.signal,
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
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

    if (inFlight[key]) {
      return inFlight[key];
    }

    inFlight[key] = (async function () {
      // 1) Same-origin Laravel API (ổn định nhất trên production)
      try {
        var res = await fetchWithTimeout(apiUrl(latN, lngN), FETCH_TIMEOUT_MS);
        if (res.ok) {
          var json = await res.json();
          if (json && json.name) {
            writeCache(key, json.name);
            return json.name;
          }
        }
      } catch (e) {
        // server timeout / network
      }

      // 2) Browser → BigDataCloud (fallback)
      try {
        var url =
          'https://api.bigdatacloud.net/data/reverse-geocode-client' +
          '?latitude=' +
          encodeURIComponent(latN) +
          '&longitude=' +
          encodeURIComponent(lngN) +
          '&localityLanguage=vi';
        var res2 = await fetchWithTimeout(url, 3000);
        if (res2.ok) {
          var data = await res2.json();
          var name = formatLocationName(data);
          if (name) {
            writeCache(key, name);
            return name;
          }
        }
      } catch (e2) {
        // ignore
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

  function shouldSkip(el) {
    var existing = (el.dataset.name || el.textContent || '').trim();
    if (!existing) return false;
    if (existing.indexOf('Đang tải') === 0) return false;
    // đã có tên thật (server prefill)
    return true;
  }

  async function fillLocationElements(root) {
    var scope = root || document;
    var cells = Array.prototype.slice.call(
      scope.querySelectorAll('[data-lat][data-lng]')
    );
    if (!cells.length) return;

    await Promise.all(
      cells.map(async function (el) {
        if (shouldSkip(el)) {
          if (el.dataset.name && el.textContent.indexOf('Đang tải') === 0) {
            el.textContent = el.dataset.name;
          }
          return;
        }

        var lat = el.dataset.lat;
        var lng = el.dataset.lng;
        if (!lat || !lng) return;

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
    fillLocationElements(document).finally(function () {
      // An toàn: không để vĩnh viễn "Đang tải..."
      var stuck = document.querySelectorAll('[data-lat][data-lng]');
      for (var i = 0; i < stuck.length; i++) {
        var el = stuck[i];
        var t = (el.textContent || '').trim();
        if (t.indexOf('Đang tải') === 0) {
          setElName(el, fallbackLabel(el.dataset.lat, el.dataset.lng));
        }
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})(window);
