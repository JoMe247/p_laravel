(function () {
  const toggle = document.getElementById("estimateFooterImgToggle");
  const overlay = document.getElementById("estimate-footer-img-overlay");

  const openOverlayBtn = document.getElementById("openEstimateFooterOverlay");
  const closeBtn = document.getElementById("closeEstimateFooterOverlay");
  const cancelBtn = document.getElementById("cancelEstimateFooterUpload");

  const addBtn = document.getElementById("btnAddEstimateFooterImage");
  const input = document.getElementById("estimateFooterImageInput");
  const saveBtn = document.getElementById("saveEstimateFooterUpload");

  const previewBox = document.getElementById("estimateFooterPreview");
  const previewImg = document.getElementById("estimateFooterPreviewImg");

  let selectedFile = null;
  let uploadedThisSession = false;

  const hadImageInitially = (openOverlayBtn && openOverlayBtn.dataset.hasImage === "1");

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
  const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute("content") || "";

  function openOverlay() {
    uploadedThisSession = false;
    if (overlay) overlay.style.display = "flex";
  }

  function closeOverlay() {
    if (overlay) overlay.style.display = "none";
    selectedFile = null;
    if (input) input.value = "";
    if (previewBox) previewBox.style.display = "none";
    if (previewImg) previewImg.src = "";

    if (!hadImageInitially && !uploadedThisSession) {
      if (toggle) toggle.checked = false;
    }
  }

  async function setEnabled(val) {
    await fetch(`${baseUrl}/estimates/estimate-footer-image/enabled`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrf,
        "Accept": "application/json",
      },
      body: JSON.stringify({ enabled: val ? "1" : "0" }),
    });
  }

  async function deleteImage() {
    await fetch(`${baseUrl}/estimates/estimate-footer-image/delete`, {
      method: "POST",
      headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
    });
  }

  if (openOverlayBtn) openOverlayBtn.addEventListener("click", openOverlay);
  if (closeBtn) closeBtn.addEventListener("click", closeOverlay);
  if (cancelBtn) cancelBtn.addEventListener("click", closeOverlay);
  if (addBtn && input) addBtn.addEventListener("click", () => input.click());

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

      const form = new FormData();
      form.append("footer_image", selectedFile);

      try {
        const res = await fetch(`${baseUrl}/estimates/estimate-footer-image`, {
          method: "POST",
          headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
          body: form,
        });

        if (!res.ok) throw new Error("Upload failed");
        await res.json();

        uploadedThisSession = true;

        if (toggle) toggle.checked = true;
        if (openOverlayBtn) openOverlayBtn.style.display = "inline-block";

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

  if (toggle) {
    toggle.addEventListener("change", async () => {
      if (toggle.checked) {
        try { await setEnabled(true); } catch (e) {}
        openOverlay();
      } else {
        const r = await Swal.fire({
          icon: "warning",
          title: "Remove footer image?",
          text: "This will delete the image and remove it from PDFs.",
          showCancelButton: true,
          confirmButtonText: "Yes, delete",
          cancelButtonText: "Cancel",
        });

        if (!r.isConfirmed) {
          toggle.checked = true;
          return;
        }

        try { await deleteImage(); } catch (e) {}

        if (openOverlayBtn) openOverlayBtn.style.display = "none";

        Swal.fire({
          icon: "success",
          title: "Deleted",
          text: "Footer image removed.",
          timer: 1400,
          showConfirmButton: false,
        });
      }
    });
  }
})();
