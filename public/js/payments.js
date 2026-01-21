(function () {
  const toggle = document.getElementById("footerImgToggle");
  const overlay = document.getElementById("footer-img-overlay");
  const closeBtn = document.getElementById("closeFooterOverlay");
  const cancelBtn = document.getElementById("cancelFooterUpload");

  const addBtn = document.getElementById("btnAddFooterImage");
  const input = document.getElementById("footerImageInput");
  const saveBtn = document.getElementById("saveFooterUpload");

  const previewBox = document.getElementById("footerPreview");
  const previewImg = document.getElementById("footerPreviewImg");

  let selectedFile = null;

  function openOverlay() {
    if (overlay) overlay.style.display = "flex";
  }

  function closeOverlay() {
    if (overlay) overlay.style.display = "none";
    if (toggle) toggle.checked = false;

    // reset
    selectedFile = null;
    if (input) input.value = "";
    if (previewBox) previewBox.style.display = "none";
    if (previewImg) previewImg.src = "";
  }

  if (toggle) {
    toggle.addEventListener("change", () => {
      if (toggle.checked) openOverlay();
      else closeOverlay();
    });
  }

  if (closeBtn) closeBtn.addEventListener("click", closeOverlay);
  if (cancelBtn) cancelBtn.addEventListener("click", closeOverlay);

  if (addBtn && input) {
    addBtn.addEventListener("click", () => input.click());
  }

  if (input) {
    input.addEventListener("change", () => {
      const file = input.files && input.files[0] ? input.files[0] : null;
      selectedFile = file;

      if (!file) {
        if (previewBox) previewBox.style.display = "none";
        return;
      }

      const url = URL.createObjectURL(file);
      if (previewImg) previewImg.src = url;
      if (previewBox) previewBox.style.display = "block";
    });
  }

  if (saveBtn) {
    saveBtn.addEventListener("click", async () => {
      if (!selectedFile) {
        Swal.fire({ icon: "warning", title: "Select an image", text: "Please choose an image first." });
        return;
      }

      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
      const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute("content") || "";

      const form = new FormData();
      form.append("footer_image", selectedFile);

      try {
        const res = await fetch(`${baseUrl}/payments/invoice-footer-image`, {
          method: "POST",
          headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
          body: form,
        });

        if (!res.ok) throw new Error("Upload failed");

        await res.json();

        Swal.fire({
          icon: "success",
          title: "Uploaded",
          text: "Footer image saved successfully.",
          timer: 1600,
          showConfirmButton: false,
        });

        closeOverlay();
      } catch (e) {
        console.error(e);
        Swal.fire({ icon: "error", title: "Error", text: "Could not upload the image." });
      }
    });
  }
})();
