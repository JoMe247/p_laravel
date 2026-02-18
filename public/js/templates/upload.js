// Select Upload-Area
const uploadArea = document.querySelector('#uploadArea')

// Select Drop-Zoon Area
const dropZoon = document.querySelector('#dropZoon');

// Loading Text
const loadingText = document.querySelector('#loadingText');

// Select File Input
const fileInput = document.querySelector('#upload'); // OJO: en tu HTML el input real es #upload

// Select Preview Image
const previewImage = document.querySelector('#previewImage');

// File-Details Area
const fileDetails = document.querySelector('#fileDetails');

// Uploaded File
const uploadedFile = document.querySelector('#uploadedFile');

// Uploaded File Info
const uploadedFileInfo = document.querySelector('#uploadedFileInfo');

// Uploaded File  Name
const uploadedFileName = document.querySelector('.uploaded-file__name');

// Uploaded File Icon
const uploadedFileIconText = document.querySelector('.uploaded-file__icon-text');

// Uploaded File Counter
const uploadedFileCounter = document.querySelector('.uploaded-file__counter');

// ToolTip Data
const toolTipData = document.querySelector('.upload-area__tooltip-data');

// File Types
const fileTypes = [
  "pdf"
];

// Append File Types Array Inside Tooltip Data
toolTipData.innerHTML = [...fileTypes].join(', .');

// When (drop-zoon) has (dragover) Event
dropZoon.addEventListener('dragover', function (event) {
  event.preventDefault();
  dropZoon.classList.add('drop-zoon--over');
});

// When (drop-zoon) has (dragleave) Event
dropZoon.addEventListener('dragleave', function (event) {
  dropZoon.classList.remove('drop-zoon--over');
});

// When (drop-zoon) has (drop) Event
dropZoon.addEventListener('drop', function (event) {
  event.preventDefault();
  dropZoon.classList.remove('drop-zoon--over');

  const file = event.dataTransfer.files[0];

  if (file) {
    uploadFile(file);
  }
});

// When (drop-zoon) has (click) Event
dropZoon.addEventListener('click', function () {
  fileInput.click();
});

// When (fileInput) has (change) Event
fileInput.addEventListener('change', function (event) {
  const file = event.target.files[0];
  if (file) {
    uploadFile(file);
  }
});

// Upload File Function
function uploadFile(file) {
  const fileType = file.type;
  const fileSize = file.size;

  if (fileValidate(fileType, fileSize)) {
    dropZoon.classList.add('drop-zoon--Uploaded');

    loadingText.style.display = "block";
    previewImage.style.display = 'none';

    setTimeout(function () {
      dropZoon.style.display = 'none';
    }, 500);

    uploadedFile.classList.remove('uploaded-file--open');
    uploadedFileInfo.classList.remove('uploaded-file__info--active');

    // Call the handleFileSelect function from template.js (antes script.js)
    // OJO: Esta función debe existir globalmente en template.js
    handleFileSelect({ target: { files: [file] } });

    setTimeout(function () {
      uploadArea.classList.add('upload-area--open');
      loadingText.style.display = "none";
      previewImage.style.display = 'none';
      fileDetails.classList.add('file-details--open');
      uploadedFile.classList.add('uploaded-file--open');
      uploadedFileInfo.classList.add('uploaded-file__info--active');
    }, 500);

    uploadedFileName.innerHTML = file.name;
    progressMove();
  }
}

// Progress Counter Increase Function
function progressMove() {
  let counter = 0;

  setTimeout(() => {
    let counterIncrease = setInterval(() => {
      if (counter === 100) {

        setTimeout(function () {
          document.getElementsByClassName("container")[0].style.display = "";
          document.getElementById("uploadArea").style.display = "none";
        }, 100);

        setTimeout(function () {
          let alturaBotones = (canvas.height - 90) * (-1);
          let leftTotales = (canvas.width - 150) * (-1);

          document.getElementById("options-bar").style.display = "";
          document.getElementById("options-bar").style.marginLeft = canvas.width + 60 + "px";
          document.getElementById("options-bar").style.marginTop = alturaBotones + "px";

          if (canvas.height >= 500 && canvas.height <= 530) {

            document.getElementById("paginasTotal").style.marginLeft = leftTotales + "px";
            document.getElementById("paginasTotal").style.marginTop = canvas.height / 2 + 8 + "px";

            document.getElementById("paginaActual").style.marginLeft = canvas.width / 2 + 90 + "px";
            document.getElementById("paginaActual").style.marginTop = canvas.height / 2 + 8 + "px";

          }

          if (canvas.height >= 580 && canvas.height <= 690) {

            document.getElementById("paginasTotal").style.marginLeft = leftTotales + "px";
            document.getElementById("paginasTotal").style.marginTop = canvas.height / 2 + 15 + "px";

            document.getElementById("paginaActual").style.marginLeft = canvas.width / 2 + 90 + "px";
            document.getElementById("paginaActual").style.marginTop = canvas.height / 2 + 15 + "px";

          }

          if (canvas.height >= 690 && canvas.height <= 750) {

            document.getElementById("paginasTotal").style.marginLeft = leftTotales + "px";
            document.getElementById("paginasTotal").style.marginTop = canvas.height / 2 + 50 + "px";

            document.getElementById("paginaActual").style.marginLeft = canvas.width / 2 + 90 + "px";
            document.getElementById("paginaActual").style.marginTop = canvas.height / 2 + 50 + "px";

          }

        }, 10);

        clearInterval(counterIncrease);
      } else {
        counter = counter + 10;
        uploadedFileCounter.innerHTML = `${counter}%`
      }
    }, 100);
  }, 600);
}

// Simple File Validate Function
function fileValidate(fileType, fileSize) {
  let isPDF = fileType === 'application/pdf';
  uploadedFileIconText.innerHTML = isPDF ? 'pdf' : '';

  if (isPDF) {
    if (fileSize <= 10000000) { // 10MB :)
      return true;
    } else {
      alert('Please Your File Should be 10 Megabytes or Less');
      return false;
    }
  } else {
    alert('Please make sure to upload A PDF File Type');
    return false;
  }
}

$('.box').on('keydown paste', function (event) {
  if ($(this).text().length === 20 && event.keyCode != 8) {
    event.preventDefault();
  }
});

function handleDrag(event) {
  if (!event.target.classList.contains('.upload-area__drop-zoon drop-zoon')) {
    document.getElementById("dropZoon").setAttribute("dragging", "true");
  }
}

function resetBox() {
  document.getElementById("dropZoon").removeAttribute("dragging");
}

window.addEventListener("dragover", function (e) {
  e = e || event;
  e.preventDefault();
}, false);

window.addEventListener("drop", function (e) {
  e = e || event;
  e.preventDefault();
}, false);
