document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');

  if (toggle && sidebar) {
    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('hidden');
    });
  }
});
// Sidebar toggle for small screens
document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');

  if (toggle && sidebar) {
    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('hidden');
    });
  }

  // Camera controls on attendance page
  const startBtn = document.getElementById('startCamera');
  const captureBtn = document.getElementById('capture');
  const video = document.getElementById('video');
  const upload = document.getElementById('upload');
  const result = document.getElementById('result');

  let stream;

  async function startCamera() {
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
      video.srcObject = stream;
    } catch (err) {
      console.error('Camera error', err);
      if (result) result.textContent = 'Không thể truy cập camera.';
    }
  }

  function stopCamera() {
    if (stream) {
      stream.getTracks().forEach(t => t.stop());
      stream = null;
    }
  }

  if (startBtn) startBtn.addEventListener('click', () => startCamera());
  if (captureBtn) captureBtn.addEventListener('click', () => {
    if (!video || !video.srcObject) {
      if (result) result.textContent = 'Camera chưa bật.';
      return;
    }

    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/jpeg');

    if (result) result.textContent = 'Đang gửi ảnh để nhận diện... (demo frontend)';
  });

  if (upload) upload.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    if (result) result.textContent = `Đã chọn ảnh: ${file.name}`;
  });
  window.addEventListener('beforeunload', stopCamera);
});
