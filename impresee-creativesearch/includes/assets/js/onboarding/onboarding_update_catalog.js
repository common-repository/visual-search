var _wseeInterval = 0;
var _wseeGotNumberSteps = false;
var _wseeCurrentStep = 1;
var _wseeTotalSteps = 0;
var _wseeNumberProducts = 0;
var _wseeStepCountElement = 'impresee-step-count';
var _wseeProgressBarElement = 'impresee-progress-bar';
var _wseeRemainingTimeElement = 'impresee-step-remaining-time';
var _wseeStepCount = document.getElementById(_wseeStepCountElement);
var _wseeProgressBar = document.getElementById(_wseeProgressBarElement);
var _wseeRemainingTime = document.getElementById(_wseeRemainingTimeElement);

function hasNumber(myString) {
  return /\d/.test(myString);
}

function _wseeRegisterCompletionEvent(event){
    if (window.dataLayer){
        window.dataLayer.push({
            'event': event,
        });
    }
}

function _wseeChangeStepStateToReady(itemId, event, with_errors){
    var _wseeItem = document.getElementById(itemId);
    var _wseeReadyState = _wseeItem.dataset.ready;
    if (_wseeReadyState === 'false'){
        _wseeItem.classList.remove('impresee-step-state-image-loading');
        _wseeItem.classList.remove('impresee-step-state-image-pending');
        if (with_errors){
            _wseeItem.classList.add('impresee-step-state-image-ready-with-error');
        }
        else {
            _wseeItem.classList.add('impresee-step-state-image-ready');
        }
        _wseeItem.parentNode.style.display = 'none';
        _wseeItem.dataset.ready = 'true';
        _wseeItem.dataset.loading = 'false';
        _wseeRegisterCompletionEvent(event);
        _wseeCurrentStep += 1;
        _wseeStepCount.innerHTML = _wseeCurrentStep + '/' + _wseeTotalSteps;
        _wseeProgressBar.style.width = '0%';
        _wseeProgressBar.innerHTML = '0%';
    }
}

function _wseeChangeStepStateToProcessing(itemId, progress, remaining_time){
    var _wseeItem = document.getElementById(itemId);
    var _wseeLoadingState = _wseeItem.dataset.loading;
    if (_wseeLoadingState === 'false'){
        _wseeItem.classList.remove('impresee-step-state-image-pending');
        _wseeItem.classList.add('impresee-step-state-image-loading');
        _wseeItem.dataset.loading = 'true';
        _wseeItem.parentNode.style.display = 'inline-block';
    }
    if ( progress.indexOf('%') < 0 ) {
        _wseeProgressBar.style.width = '100%';
        _wseeProgressBar.innerHTML = progress;
    } else {
        _wseeProgressBar.style.width = progress;
        _wseeProgressBar.innerHTML = progress;
    }
    if (!hasNumber(remaining_time)){
        _wseeRemainingTime.innerHTML = '<span style="font-weight: bold; class="impresee-step-state-image-loading">Time might take a couple of minutes</span>';
    }
    else{
        _wseeRemainingTime.innerHTML = '<span style="font-weight: bold;">Time remaining for the step to finish:</span> ' + remaining_time;
    }
}

function _wseeUpdateView(catalogUpdateInfo){
    if (catalogUpdateInfo.get_catalog.processing && !catalogUpdateInfo.get_catalog.finished) {
        _wseeChangeStepStateToProcessing('get_catalog', catalogUpdateInfo.get_catalog.progress,
        catalogUpdateInfo.get_catalog.remaining_time);
    }
    else if (catalogUpdateInfo.get_catalog.finished) {
        _wseeChangeStepStateToReady('get_catalog', 'finish_downloaded_catalog',
            catalogUpdateInfo.get_catalog.with_errors);
    }

    if (catalogUpdateInfo.download_images && catalogUpdateInfo.download_images.processing && !catalogUpdateInfo.download_images.finished) {
        _wseeChangeStepStateToProcessing('download_images', catalogUpdateInfo.download_images.progress,
        catalogUpdateInfo.download_images.remaining_time);
    }
    else if (catalogUpdateInfo.download_images && catalogUpdateInfo.download_images.finished){
        _wseeChangeStepStateToReady('download_images', 'finished_downloading_images',
            catalogUpdateInfo.download_images.with_errors);
    }

    if (catalogUpdateInfo.thumbnails && catalogUpdateInfo.thumbnails.processing && !catalogUpdateInfo.thumbnails.finished) {
        _wseeChangeStepStateToProcessing('thumbnails', catalogUpdateInfo.thumbnails.progress,
        catalogUpdateInfo.thumbnails.remaining_time);
    }
    else if (catalogUpdateInfo.thumbnails && catalogUpdateInfo.thumbnails.finished){
        _wseeChangeStepStateToReady('thumbnails', 'finished_computing_thumbnails',
            catalogUpdateInfo.thumbnails.with_errors);
    }

    if (catalogUpdateInfo.photo_2020 && catalogUpdateInfo.photo_2020.processing && !catalogUpdateInfo.photo_2020.finished) {
        _wseeChangeStepStateToProcessing('photo_visual', catalogUpdateInfo.photo_2020.progress,
        catalogUpdateInfo.photo_2020.remaining_time);
    }
    else if (catalogUpdateInfo.photo_2020 && catalogUpdateInfo.photo_2020.finished){
        _wseeChangeStepStateToReady('photo_visual', 'finished_computing_photo_visual_descriptors',
            catalogUpdateInfo.photo_2020.with_errors);
    }

    if (catalogUpdateInfo.clothing_color_2020 && catalogUpdateInfo.clothing_color_2020.processing && !catalogUpdateInfo.clothing_color_2020.finished) {
        _wseeChangeStepStateToProcessing('photo_visual', catalogUpdateInfo.clothing_color_2020.progress,
        catalogUpdateInfo.clothing_color_2020.remaining_time);
    }
    else if (catalogUpdateInfo.clothing_color_2020 && catalogUpdateInfo.clothing_color_2020.finished){
        _wseeChangeStepStateToReady('photo_visual', 'finished_computing_photo_visual_descriptors',
            catalogUpdateInfo.clothing_color_2020.with_errors);
    }

    if (catalogUpdateInfo.sketch_2020 && catalogUpdateInfo.sketch_2020.processing && !catalogUpdateInfo.sketch_2020.finished) {
        _wseeChangeStepStateToProcessing('sketch_visual', catalogUpdateInfo.sketch_2020.progress,
        catalogUpdateInfo.sketch_2020.remaining_time);
    }
    else if (catalogUpdateInfo.sketch_2020 && catalogUpdateInfo.sketch_2020.finished){
        _wseeChangeStepStateToReady('sketch_visual', 'finished_computing_sketch_descriptors',
            catalogUpdateInfo.sketch_2020.with_errors);
    }

    if (catalogUpdateInfo.refresh && catalogUpdateInfo.refresh.processing && !catalogUpdateInfo.refresh.finished) {
        _wseeChangeStepStateToProcessing('refresh', catalogUpdateInfo.refresh.progress,
        catalogUpdateInfo.refresh.remaining_time);
    }
    else if (catalogUpdateInfo.refresh && catalogUpdateInfo.refresh.finished){
        _wseeChangeStepStateToReady('refresh', 'finish_refreshing_servers',
            catalogUpdateInfo.refresh.with_errors);
    }
    // If all steps have been completed
    if (catalogUpdateInfo.finish && catalogUpdateInfo.finish.finished){
        _wseeFinishedUpdating();    
    }
}

function _wseeFinishedUpdating(){
    // Hide the progress information
    _wseeStepCount.innerHTML = '';
    _wseeStepCount.style.display = "none";
    document.querySelector('.impresee-progress-bar-container').style.display = 'none';
    document.querySelector('.impresee-step-progress').style.margin = '1em auto';
    var processCompletionInformation = document.querySelector('.impresee-process-completion-information');
    if (processCompletionInformation !== null) {
        processCompletionInformation.style.display = 'none';
    }
    _wseeProgressBar.style.display = 'none';
    _wseeRemainingTime.style.display = 'none';
    if(document.querySelector('#wsee-finished') != null){
        return;
    }
    var _wseeReadyButton = document.getElementById('next-screen-button');
    var _wseeLoading = document.getElementById('impresee-loading-gif');
    _wseeLoading.style.display = 'none';
    _wseeReadyButton.classList.add('impresee-ready-button');
    _wseeReadyButton.classList.remove('impresee-loading-button');
    _wseeReadyButton.innerHTML = 'Next';

    var _wseeContainer = document.querySelector('.impresee-bar-step-container');
    var _wseeEndMessageContainer = document.createElement('div');
    _wseeEndMessageContainer.style.display = "flex";
    _wseeEndMessageContainer.style.flexDirection = "column";
    _wseeEndMessageContainer.style.alignItems = "center";
    _wseeEndMessageContainer.style.borderRadius = "5px";
    _wseeEndMessageContainer.id = 'wsee-finished';
    var _wseeEndImageContainer = document.createElement('div');
    _wseeEndImageContainer.style.height = "80px";
    _wseeEndImageContainer.style.width = "80px";
    var _wseeEndImage = document.createElement('img');
    _wseeEndImage.style.maxHeight = "100%";
    _wseeEndImage.style.maxWidth = "100%";
    var _wseeEndMessage = document.createElement('p');
    var _wseeWereDoneMessage = document.createElement('span');
    if (_wseeNumberProducts > 0){
        _wseeEndMessage.innerHTML = "Your catalog is ready to be used";
        _wseeEndImage.src = successImage;
        _wseeWereDoneMessage.innerHTML = 'We\'re done 😀'
    } else {
       _wseeEndMessage.innerHTML = "We couldn't find any products in your catalog!";
       _wseeEndImage.src = warningImage;
       _wseeWereDoneMessage.innerHTML = 'Something went wrong 😔'
    }
    _wseeEndImageContainer.appendChild(_wseeEndImage);
    _wseeEndMessageContainer.appendChild(_wseeEndImageContainer);
    _wseeEndMessageContainer.appendChild(_wseeWereDoneMessage);
    _wseeEndMessageContainer.appendChild(_wseeEndMessage);
    _wseeContainer.appendChild(_wseeEndMessageContainer);
    clearInterval(_wseeInterval);
}

function _wseeRefreshScreen(){
    var _wseeUpdateRequest = new XMLHttpRequest();
    // timeout of ten seconds
    _wseeUpdateRequest.timeout = 10000;
    _wseeUpdateRequest.onreadystatechange = function() {
        if (_wseeUpdateRequest.readyState === 4 && _wseeUpdateRequest.status === 200) {
            var _wseeUpdateCatalogResponse = JSON.parse(this.responseText);
            _wseeNumberProducts = _wseeUpdateCatalogResponse.number_of_products;
            var _wseeUpdateSteps = _wseeUpdateCatalogResponse.steps;
            var _wseeParsedSteps = {};
            for ( var _wseeStep of _wseeUpdateSteps ){
                if (!_wseeGotNumberSteps){
                    _wseeTotalSteps += 1;
                }
                if (_wseeStep.code.indexOf('clothing') > 0)
                {
                    _wseeStep.code =  'clothing_color_2020';
                }
                _wseeParsedSteps[_wseeStep.code] = {
                    processing: _wseeStep.processing,
                    progress: _wseeStep.progress,
                    remaining_time: _wseeStep.remaining_time,
                    finished: _wseeStep.finished,
                    with_errors:  _wseeStep.with_errors
                 };
            }
            _wseeGotNumberSteps = true;
            _wseeStepCount.innerHTML = _wseeCurrentStep + '/' + _wseeTotalSteps;
            _wseeUpdateView(_wseeParsedSteps);
        }
    }
    _wseeUpdateRequest.open('GET', _wseeUpdateUrl);
    _wseeUpdateRequest.send();
}

if(_wseeCompleteUpdate){
    _wseeFinishedUpdating();   
} else {
    _wseeInterval = setInterval(_wseeRefreshScreen, 2000);
    _wseeRefreshScreen();
}

