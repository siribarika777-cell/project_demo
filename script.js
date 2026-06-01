// CAReva - Main JavaScript

document.addEventListener('DOMContentLoaded', function () {

    // ── NAVBAR SCROLL EFFECT ──
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
    }

    // ── AUTO-DISMISS ALERTS ──
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            alert.style.transition = 'all 0.4s ease';
            setTimeout(() => alert.remove(), 400);
        }, 4000);
    });

    // ── SIDEBAR TOGGLE (Mobile) ──
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    // ── IMAGE PREVIEW ──
    const carImagesInput = document.getElementById('car_images');
    const previewGrid = document.getElementById('imgPreviewGrid');
    if (carImagesInput && previewGrid) {
        carImagesInput.addEventListener('change', function () {
            previewGrid.innerHTML = '';
            Array.from(this.files).forEach(file => {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = e => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-preview';
                    previewGrid.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    const plateInput = document.getElementById('number_plate_image');
    const platePrev = document.getElementById('platePrev');
    if (plateInput && platePrev) {
        plateInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                platePrev.src = e.target.result;
                platePrev.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }

    // ── PASSWORD TOGGLE ──
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            this.textContent = input.type === 'password' ? '👁' : '🙈';
        });
    });

    // ── DEPRECIATION CHART ──
    const chartCanvas = document.getElementById('depreciationChart');
    if (chartCanvas && window.chartData) {
        const ctx = chartCanvas.getContext('2d');
        const data = window.chartData;
        const labels = ['Now', '5 Years', '10 Years', '20 Years'];
        const values = [data.current, data.v5, data.v10, data.v20];
        drawLineChart(ctx, labels, values, chartCanvas.width, chartCanvas.height);
    }

    // ── GEOLOCATION ──
    const geoBtn = document.getElementById('detectLocation');
    if (geoBtn) {
        geoBtn.addEventListener('click', () => {
            geoBtn.textContent = '📡 Detecting...';
            geoBtn.disabled = true;
            navigator.geolocation.getCurrentPosition(
                pos => {
                    document.getElementById('user_lat').value = pos.coords.latitude;
                    document.getElementById('user_lng').value = pos.coords.longitude;
                    document.getElementById('nearbyForm').submit();
                },
                err => {
                    alert('Could not detect location: ' + err.message);
                    geoBtn.textContent = '📍 Detect My Location';
                    geoBtn.disabled = false;
                }
            );
        });
    }

    // ── ANIMATE STAT NUMBERS ──
    document.querySelectorAll('.stat-value[data-count]').forEach(el => {
        const target = parseInt(el.dataset.count);
        let current = 0;
        const step = Math.ceil(target / 40);
        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = current.toLocaleString();
            if (current >= target) clearInterval(timer);
        }, 30);
    });

    // ── WISHLIST TOGGLE ──
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.stopPropagation();
            const carId = this.dataset.car;
            const resp = await fetch('wishlist-toggle.php?car_id=' + carId);
            const json = await resp.json();
            this.textContent = json.added ? '❤️' : '🤍';
            this.classList.toggle('active', json.added);
        });
    });

    // ── SMOOTH SCROLL (landing page) ──
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // ── CONFIRMATION DIALOGS ──
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm)) e.preventDefault();
        });
    });

    // ── FILTER FORM AUTO-SUBMIT ──
    document.querySelectorAll('.auto-submit select').forEach(sel => {
        sel.addEventListener('change', () => sel.closest('form').submit());
    });
});

// ── CANVAS LINE CHART ──
function drawLineChart(ctx, labels, values, width, height) {
    const pad = { top: 30, right: 30, bottom: 50, left: 80 };
    const w = width - pad.left - pad.right;
    const h = height - pad.top - pad.bottom;
    const max = Math.max(...values) * 1.1;
    const min = 0;

    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = '#080c12';
    ctx.fillRect(0, 0, width, height);

    // Grid lines
    ctx.strokeStyle = 'rgba(0,212,255,0.1)';
    ctx.lineWidth = 1;
    for (let i = 0; i <= 4; i++) {
        const y = pad.top + (h / 4) * i;
        ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(pad.left + w, y); ctx.stroke();
        const val = max - ((max - min) / 4) * i;
        ctx.fillStyle = '#445566';
        ctx.font = '11px Exo 2, sans-serif';
        ctx.textAlign = 'right';
        ctx.fillText('₹' + formatLakh(val), pad.left - 8, y + 4);
    }

    // Area gradient
    const grad = ctx.createLinearGradient(0, pad.top, 0, pad.top + h);
    grad.addColorStop(0, 'rgba(0,212,255,0.25)');
    grad.addColorStop(1, 'rgba(0,212,255,0.01)');
    ctx.beginPath();
    values.forEach((v, i) => {
        const x = pad.left + (w / (values.length - 1)) * i;
        const y = pad.top + h - ((v - min) / (max - min)) * h;
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.lineTo(pad.left + w, pad.top + h);
    ctx.lineTo(pad.left, pad.top + h);
    ctx.closePath();
    ctx.fillStyle = grad;
    ctx.fill();

    // Line
    ctx.beginPath();
    ctx.strokeStyle = '#00d4ff';
    ctx.lineWidth = 2.5;
    ctx.lineJoin = 'round';
    values.forEach((v, i) => {
        const x = pad.left + (w / (values.length - 1)) * i;
        const y = pad.top + h - ((v - min) / (max - min)) * h;
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.stroke();

    // Points & labels
    values.forEach((v, i) => {
        const x = pad.left + (w / (values.length - 1)) * i;
        const y = pad.top + h - ((v - min) / (max - min)) * h;
        ctx.beginPath();
        ctx.arc(x, y, 5, 0, Math.PI * 2);
        ctx.fillStyle = '#00d4ff';
        ctx.shadowColor = '#00d4ff';
        ctx.shadowBlur = 10;
        ctx.fill();
        ctx.shadowBlur = 0;
        ctx.fillStyle = '#f0f4f8';
        ctx.font = '11px Exo 2, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(labels[i], x, pad.top + h + 20);
        ctx.fillStyle = '#00d4ff';
        ctx.fillText('₹' + formatLakh(v), x, y - 12);
    });
}

function formatLakh(val) {
    if (val >= 10000000) return (val / 10000000).toFixed(2) + ' Cr';
    if (val >= 100000) return (val / 100000).toFixed(2) + ' L';
    return Math.round(val).toLocaleString();
}