(function () {
  const toggle = document.getElementById("footerImgToggle");
  const overlay = document.getElementById("footer-img-overlay");

  const openOverlayBtn = document.getElementById("openFooterOverlay");
  const closeBtn = document.getElementById("closeFooterOverlay");
  const cancelBtn = document.getElementById("cancelFooterUpload");

  const addBtn = document.getElementById("btnAddFooterImage");
  const input = document.getElementById("footerImageInput");
  const saveBtn = document.getElementById("saveFooterUpload");

  const previewBox = document.getElementById("footerPreview");
  const previewImg = document.getElementById("footerPreviewImg");
  const selectedFileName = document.getElementById("footerSelectedFileName");

  let selectedFile = null;
  let uploadedThisSession = false;
  let hadImageInitially = (openOverlayBtn && openOverlayBtn.dataset.hasImage === "1");

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
  const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute("content") || "";

  function openOverlay() {
    uploadedThisSession = false;
    if (overlay) overlay.style.display = "flex";
  }

  function resetDropZone() {
    if (input) input.value = "";
    if (addBtn) addBtn.classList.remove("dragover", "has-file");
    if (selectedFileName) selectedFileName.textContent = "No file selected";
  }

  function closeOverlay() {
    if (overlay) overlay.style.display = "none";

    selectedFile = null;

    if (previewBox) previewBox.style.display = "none";
    if (previewImg) previewImg.src = "";

    resetDropZone();

    if (!hadImageInitially && !uploadedThisSession) {
      if (toggle) toggle.checked = false;
    }
  }

  async function setEnabled(val) {
    await fetch(`${baseUrl}/payments/invoice-footer-image/enabled`, {
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
    await fetch(`${baseUrl}/payments/invoice-footer-image/delete`, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrf,
        "Accept": "application/json",
      },
    });
  }

  function setSingleFileToInput(fileInput, file) {
    if (!fileInput || !file) return;

    try {
      const dt = new DataTransfer();
      dt.items.add(file);
      fileInput.files = dt.files;
    } catch (err) {
      console.error("Could not assign dropped file to input:", err);
    }
  }

  function isValidImage(file) {
    return file && file.type && file.type.startsWith("image/");
  }

  function showPreview(file) {
    selectedFile = file || null;

    if (!file) {
      if (previewBox) previewBox.style.display = "none";
      if (previewImg) previewImg.src = "";
      if (selectedFileName) selectedFileName.textContent = "No file selected";
      if (addBtn) addBtn.classList.remove("has-file");
      return;
    }

    if (selectedFileName) {
      selectedFileName.textContent = file.name;
    }

    if (addBtn) {
      addBtn.classList.add("has-file");
    }

    const url = URL.createObjectURL(file);

    if (previewImg) previewImg.src = url;
    if (previewBox) previewBox.style.display = "block";
  }

  function handleImageFile(file) {
    if (!file) return;

    if (!isValidImage(file)) {
      Swal.fire({
        icon: "warning",
        title: "Invalid file",
        text: "Please drop an image file only."
      });
      return;
    }

    if (input) {
      setSingleFileToInput(input, file);
    }

    showPreview(file);
  }

  if (openOverlayBtn) {
    openOverlayBtn.addEventListener("click", openOverlay);
  }

  if (closeBtn) closeBtn.addEventListener("click", closeOverlay);
  if (cancelBtn) cancelBtn.addEventListener("click", closeOverlay);

  if (addBtn && input) {
    addBtn.addEventListener("click", () => input.click());

    addBtn.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        input.click();
      }
    });
  }

  if (input) {
    input.addEventListener("change", () => {
      const file = input.files && input.files[0] ? input.files[0] : null;
      handleImageFile(file);
    });
  }

  if (addBtn) {
    ["dragenter", "dragover", "dragleave", "drop"].forEach(eventName => {
      addBtn.addEventListener(eventName, function (e) {
        e.preventDefault();
        e.stopPropagation();
      });
    });

    ["dragenter", "dragover"].forEach(eventName => {
      addBtn.addEventListener(eventName, function () {
        addBtn.classList.add("dragover");
      });
    });

    ["dragleave", "drop"].forEach(eventName => {
      addBtn.addEventListener(eventName, function () {
        addBtn.classList.remove("dragover");
      });
    });

    addBtn.addEventListener("drop", function (e) {
      const files = e.dataTransfer.files;
      if (!files || !files.length) return;

      handleImageFile(files[0]);
    });
  }

  if (saveBtn) {
    saveBtn.addEventListener("click", async () => {
      if (!selectedFile) {
        Swal.fire({
          icon: "warning",
          title: "Select an image",
          text: "Please choose an image first."
        });
        return;
      }

      const form = new FormData();
      form.append("footer_image", selectedFile);

      try {
        const res = await fetch(`${baseUrl}/payments/invoice-footer-image`, {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": csrf,
            "Accept": "application/json"
          },
          body: form,
        });

        if (!res.ok) throw new Error("Upload failed");
        await res.json();

        uploadedThisSession = true;
        hadImageInitially = true;

        if (toggle) toggle.checked = true;
        if (openOverlayBtn) {
          openOverlayBtn.style.display = "inline-block";
          openOverlayBtn.dataset.hasImage = "1";
        }

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
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Could not upload the image."
        });
      }
    });
  }

  if (toggle) {
    toggle.addEventListener("change", async () => {
      if (toggle.checked) {
        try {
          await setEnabled(true);
        } catch (e) {}

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

        try {
          await deleteImage();
        } catch (e) {}

        if (openOverlayBtn) {
          openOverlayBtn.style.display = "none";
          openOverlayBtn.dataset.hasImage = "0";
        }

        hadImageInitially = false;
        selectedFile = null;

        if (previewBox) previewBox.style.display = "none";
        if (previewImg) previewImg.src = "";

        resetDropZone();

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