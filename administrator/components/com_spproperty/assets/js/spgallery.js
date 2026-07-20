// ************************ Drag and drop ***************** //
var dropArea = document.querySelector('#' + dropAreaId);
var gallery = document.querySelector('#' + dropAreaId + ' .gallery');
var field_input = document.querySelector('#' + dropAreaId + ' #' + field_id);
var file_field = document.querySelector(
    '#' + dropAreaId + ' #fileElem_' + field_id
);
var upload_image = document.querySelector('#' + dropAreaId + ' .upload-image');
var upload_btn = document.querySelector('#' + dropAreaId + ' .upload-btn');
var remove_image_el = document.querySelectorAll(
    '#' + dropAreaId + ' .remove-image'
);
var el_image_wrapper = document.querySelectorAll(
    '#' + dropAreaId + ' .image-wrapper.saved-src'
);
var json_array = [];
var files = [];
var images = [];
var inner_progress_bars = [];
var max_index = 0;
let uploadProgress = [];
var gallery_folder = document.querySelector('input[name="jform[gallery_folder]"]:checked').value;
var folder_name = document.querySelector('input[name="jform[gallery_folder_name]"]').value;
var gallery_field = document.querySelector('#jform_gallery');
var gallery_images = [];

// Load JS file for image sortable.
let scriptElement = document.createElement("script");
scriptElement.setAttribute("src", "https://code.jquery.com/ui/1.13.2/jquery-ui.js");
document.body.appendChild(scriptElement);

window.addEventListener('load', init, false);

//init function. initiate the maximum index value for saved images
function init()
{
    max_index = updateIndexMax();
    jQuery(`.gallery`).sortable({
        update: function () {

            var img = jQuery(this).find('img');
            Object.values(img.map(function (index) {

                index = 'gallery' + index;
                if ((jQuery(this).attr('data-photo')) != undefined && jQuery(this).attr('data-alt_text') != undefined) {
                    gallery_images[index] = { photo: jQuery(this).attr('data-photo'), alt_text: jQuery(this).attr('data-alt_text') }
                }
            }));
            gallery_field.value = JSON.stringify({ ...gallery_images })
        }
    });

}

//maximum index number for saved files only
function updateIndexMax()
{
    let max = 0;
    if (el_image_wrapper.length > 0) {
        let indexes = [];
        el_image_wrapper.forEach(function (el) {
            indexes.push(el.dataset.index);
        });
        max = Math.max(...indexes);
    }
    return max;
}

/**
 * Maximum index value for all images uploaded
 */
function maxIndex()
{
    let el_img_wrp = document.querySelectorAll(
        '#' + dropAreaId + ' .image-wrapper'
    );
    let arr = [];
    let maxInd = 0,
        maxEl;
    el_img_wrp.forEach(function (v) {
        if (v.dataset.index >= maxInd) {
            maxEl = v;
            maxInd = v.dataset.index;
        }
    });
    return [maxInd, maxEl];
}
/**
 *
 * @param {*} removedIndex
 * @param {*} obj
 *
 * @return object
 *
 * When a image has been removed it swap it's index number with highest index.
 * i.e. if you remove index 3 and there are 6 images then the 6th index
 * replaced with index 3.
 * This method remains the index number always 1 to number of images.
 */

function swapElement(removedIndex, obj)
{
    let maxi = maxIndex();
    maxi[1].dataset.index = removedIndex;
    let removable_key, max_key;
    removable_key = field_name + removedIndex;
    max_key = field_name + maxi[0];

    if (obj.hasOwnProperty(removable_key)) {
        delete obj[removable_key];
    }
    //renaming object property
    //max_key => old_key, removable_key => new_key
    if (max_key !== removable_key) {
        Object.defineProperty(
            obj,
            removable_key,
            Object.getOwnPropertyDescriptor(obj, max_key)
        );
        delete obj[max_key];
    }
    return obj;
}

//Test file is uploaded or not
function uploadCompleted(index)
{
    let el = document.querySelectorAll(
        '#' + dropAreaId + ' .image-wrapper.unsaved'
    );
    if (el.length > 0) {
        el.forEach(function (v) {
            if (v.dataset.index == index) {
                v.classList.remove('unsaved');
            }
        });
    }
}

//show or hide upload button
function showUploadButton()
{
    let el = document.querySelectorAll(
        '#' + dropAreaId + ' .image-wrapper.unsaved'
    );
    if (el.length > 0) {
        return true;
    }
    return false;
}

// Prevent default drag behaviors
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function (eventName) {
    dropArea.addEventListener(eventName, preventDefaults, false);
    document.body.addEventListener(eventName, preventDefaults, false);
});

/**
 * Highlight drop area when item is dragged over it
 */
['dragenter', 'dragover'].forEach(function (eventName) {
    dropArea.addEventListener(eventName, highlight, false);
});

/**
 * Unhighlight when drag leave or dorp
 */
['dragleave', 'drop'].forEach(function (eventName) {
    dropArea.addEventListener(eventName, unhighlight, false);
});

/**
 * Click event fired when click to upload image(s)
 * This just fire click to the file input field
 */
upload_image.addEventListener(
    'click',
    function (e) {
        e.preventDefault();
        e.stopPropagation();
        file_field.click();
    },
    false
);

/**
 * stop propagation
 */
upload_image.addEventListener(
    'click',
    function (e) {
        e.stopPropagation();
    },
    false
);

/**
 * Click event for upload button.
 * This is responsible for uploading files to server.
 */
upload_btn.addEventListener(
    'click',
    function (e) {
        e.preventDefault();
        uploadAllFiles(files);
    },
    false
);

/**
 * drop event
 */
dropArea.addEventListener('drop', handleDrop, false);

/**
 *
 * @param {*} e
 * all prevent default
 */
function preventDefaults(e)
{
    e.preventDefault();
    e.stopPropagation();
}

/**
 *
 * @param {*} e
 * add highlight class
 */
function highlight(e)
{
    dropArea.classList.add('highlight');
}

/**
 *
 * @param {*} e
 * remove highlight class
 */
function unhighlight(e)
{
    dropArea.classList.remove('highlight');
}

/**
 *
 * @param {*} e
 * Drop handler function
 * handles files which are dropped.
 */
function handleDrop(e)
{
    var dt = e.dataTransfer;
    let tempFiles = dt.files;
    tempFiles = [...tempFiles];

    tempFiles.forEach(function (v) {
        files.push(v);
    });

    handleFiles(files);
}

/**
 * Clear gallery when new files uploaded and reagrange the view.
 */
function cleanGallery()
{
    let cleanable = document.querySelectorAll(
        '#' + dropAreaId + ' .image-wrapper:not(.saved-src)'
    );
    cleanable.forEach(function (v) {
        v.remove();
    });
}

/**
 *
 * @param {*} fileList
 * Handling file upload
 */
function handleFiles(fileList)
{
    cleanGallery();
    if (fileList.constructor !== Array) {
        let tempList = [...fileList];
        tempList.forEach(function (v) {
            files.push(v);
        });
    }
    files.forEach(previewFile);
}

/**
 *
 * @param {*} files
 * Upload all files.
 */
function uploadAllFiles(files)
{
    files.forEach(uploadFile);
}

/**
 *
 * @param {*} file file to preview
 * @param {*} i index number of the file
 *
 * Preview the image on the gallery.
 */
function previewFile(file, i)
{
    let reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onloadend = function () {
        //creating image wrapper
        let img_wrapper = document.createElement('div');
        img_wrapper.setAttribute('class', 'image-wrapper unsaved');
        img_wrapper.setAttribute('data-index', i + max_index + 1);
        img_wrapper.setAttribute('data-src', '');

        //creating image
        let img = document.createElement('img');
        img.src = reader.result;
        img.setAttribute('id',file.lastModified);
        img_wrapper.appendChild(img);

        //creating image name
        let image_name = document.createElement('span');
        image_name.setAttribute('class', 'image-label');
        image_name.innerHTML = file.name;
        //img_wrapper.appendChild(image_name);

        //creating progress bar
        let inner_progress = document.createElement('progress');
        inner_progress.setAttribute('class', 'inner-progress');
        inner_progress.setAttribute('min', 0);
        inner_progress.setAttribute('max', 100);
        inner_progress.setAttribute('value', 0);
        inner_progress.style.display = 'none';
        img_wrapper.appendChild(inner_progress);

        inner_progress_bars[i] = inner_progress;

        //creating remove image
        //remove image wrapper
        let remove_image_wrapper = document.createElement('div');
        remove_image_wrapper.setAttribute('class', 'remove-image-wrapper');
        img_wrapper.appendChild(remove_image_wrapper);

        //cross holder
        let cross_holder = document.createElement('div');
        cross_holder.setAttribute('class', 'cross-holder');
        remove_image_wrapper.appendChild(cross_holder);

        let remove_image = document.createElement('a');
        remove_image.setAttribute('class', 'remove-image spgallery-close');
        remove_image.setAttribute('href', 'javascript:');

        remove_image_wrapper.onclick = removeHandler(file, img_wrapper);
        cross_holder.appendChild(remove_image);

        document
            .querySelector('#' + dropAreaId + ' .gallery')
            .appendChild(img_wrapper);

        if (showUploadButton()) {
            upload_btn.style.display = 'inline-block';
        } else {
            upload_btn.style.display = 'none';
        }
    };
}

//remove files for saved images
remove_image_el.forEach(function (el) {
    el.addEventListener('click', removeSavedHandler, false);
});

/**
 *
 * @param {*} e
 * Remove saved images , i.e. which images are uploaded and saved previously.
 */
function removeSavedHandler(e)
{
    e.preventDefault();
    let src = this.parentElement.parentElement.parentElement.dataset.src;
    let img_wrp = this.parentElement.parentElement.parentElement;
    if (src != undefined && src != '') {
        removeSavedData(src, img_wrp);
    }
}


function removeSavedData(file_src, wrapper)
{
    let url = host + '&task=spgallery.removeSavedFile';
    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('X-Request-With', 'XMLHttpRequest');

    xhr.addEventListener('readystatechange', function (e) {
        if (xhr.readyState == 4 && xhr.status == 200) {
            if (field_input.value != '') {
                let index_value = wrapper.dataset.index;
                let field_value = JSON.parse(field_input.value);
                field_value = swapElement(index_value, field_value);
                field_input.value = JSON.stringify(field_value);
            }
            max_index = updateIndexMax();
            wrapper.remove();

            if (showUploadButton()) {
                upload_btn.style.display = 'inline-block';
            } else {
                upload_btn.style.display = 'none';
            }

            if (gallery.childElementCount <= 0) {
                field_input.value = '';
            }
        } else if (xhr.readyState == 4 && xhr.status != 200) {
            console.error('Error removing file!');
        }
    });

    formData.append('image_src', file_src);
    xhr.send(formData);
}



function removeHandler(file, img_wrapper)
{
    return function (event) {
        event.preventDefault();

        let remove_url = host + '&task=spgallery.removeFile';
        let xhr = new XMLHttpRequest();
        let formData = new FormData();
        xhr.open('POST', remove_url, true);
        xhr.setRequestHeader('X-Request-With', 'XMLHttpRequest');

        xhr.addEventListener('readystatechange', function (e) {
            if (xhr.readyState == 4 && xhr.status == 200) {
                if (xhr.responseText == '1') {
                    if (field_input.value != '') {
                        let index_value = parseInt(img_wrapper.dataset.index);
                        let field_value = JSON.parse(field_input.value);
                        field_value = swapElement(index_value, field_value);
                        field_input.value = JSON.stringify(field_value);
                    }
                    files.splice(
                        parseInt(img_wrapper.dataset.index) - (max_index + 1),
                        1
                    );

                    img_wrapper.remove();
                } else if (xhr.responseText == '-1') {
                    files.splice(
                        parseInt(img_wrapper.dataset.index) - (max_index + 1),
                        1
                    );
                    cleanGallery();
                    inner_progress_bars = [];
                    files.forEach(previewFile);
                }

                if (showUploadButton()) {
                    upload_btn.style.display = 'inline-block';
                } else {
                    upload_btn.style.display = 'none';
                }

                if (gallery.childElementCount <= 0) {
                    field_input.value = '';
                }
            } else if (xhr.readyState == 4 && xhr.status != 200) {
                console.error('Failed removing');
            }
        });

        formData.append('removable_file', file);
        formData.append('folder_name', folder_name)
        formData.append('gallery_folder', gallery_folder)
        xhr.send(formData);
    };
}

/**
 *
 * @param {*} file
 * @param {*} i
 *
 * Upload a single file
 */
function uploadFile(file, i)
{
    var url = host + '&task=spgallery.uploadFiles';
    var xhr = new XMLHttpRequest();
    var formData = new FormData();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    inner_progress_bars.forEach(function (bar) {
        bar.style.display = 'block';
    });

    // Update progress (can be used to show progress indicator)
    xhr.upload.addEventListener('progress', function (e) {
        inner_progress_bars[i].max = e.total;
        inner_progress_bars[i].value = (e.loaded * 100) / e.total;
    });

    xhr.upload.addEventListener('loadstart', function (e) {
        inner_progress_bars[i].value = 0;
    });

    xhr.upload.addEventListener('loadend', function (e) {
        inner_progress_bars[i].value = e.loaded;
    });

    xhr.addEventListener('readystatechange', function (e) {
        if (xhr.readyState == 4 && xhr.status == 200) {
            let index_name = field_name + '' + (i + max_index + 1);
            if (field_input.value != '') {
                json_array = JSON.parse(field_input.value);
                json_array[index_name] = JSON.parse(xhr.responseText);
            } else {
                json_array[index_name] = JSON.parse(xhr.responseText);
            }

            // set image attributes.
            let image        = document.getElementById(file.lastModified);
            let responseData = JSON.parse(xhr.responseText);

            image.setAttribute('data-photo',responseData.photo);
            image.setAttribute('data-alt_text',responseData.alt_text);

            let json_output = JSON.stringify({ ...json_array });
            field_input.value = json_output;
            uploadCompleted(i + max_index + 1);

            if (!showUploadButton()) {
                upload_btn.style.display = 'none';
                inner_progress_bars.forEach(function (bar) {
                    bar.classList.add('completed');
                });
            }
        } else if (xhr.readyState == 4 && xhr.status != 200) {
            console.error('fail upload');
        }
    });

    formData.append('gallery_file', file);
    formData.append('folder_name', folder_name)
    formData.append('gallery_folder', gallery_folder)

    xhr.send(formData);
}