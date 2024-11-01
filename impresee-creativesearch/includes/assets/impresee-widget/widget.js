function documentReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

var _wseeImpreseeSearchBarContainer = document.createElement('div');
var _wseeImpreseeSearchBar = document.createElement('ul');

function _wseeAddWidget() {
    var q = document.querySelectorAll('input[type="search"]:not([name="q"]),[name="q"],input[name=s]');
    for (var i = 0; i < q.length ; i++){
        var searchBar = q[i];
        searchBar.onfocus = function () {
            this.classList.add('impresee-current-search-bar');
            _wseeImpreseeSearchBarContainer.classList.add('impresee-hidden-bar');
            setTimeout( function() {
                var searchBar = document.querySelector('.impresee-current-search-bar');
                if (!searchBar) {
                    var searchBars = document.querySelectorAll('input[type="search"]:not([name="q"]),[name="q"],input[name=s]');
                    if (searchBars.length > 0) {
                        searchBar = searchBars[0];
                    }
                    else {
                        return;
                    }
                }
                var searchBarRect = searchBar.getBoundingClientRect();
                var _wseeOffsetTop = Math.floor(searchBarRect.y + searchBarRect.height) + window.scrollY;
                var _wseeOffsetLeft = Math.floor(searchBarRect.x + searchBarRect.width) + window.scrollX;
                var transformValue =  'translate3d(calc(' + _wseeOffsetLeft + 'px - 14em),'
                    + _wseeOffsetTop + 'px, 0px)';
                _wseeImpreseeSearchBarContainer.style.transform = transformValue;
                _wseeImpreseeSearchBarContainer.classList.remove('impresee-hidden-bar');
                searchBar.classList.remove('impresee-current-search-bar');
                document.addEventListener('click', function(event) {
                var isClickInsideSearchBar = searchBar.parentElement.contains(event.target);
                var isClickInsideImpreseeSearchBar = _wseeImpreseeSearchBar.contains(event.target);
                    if (!isClickInsideSearchBar && !isClickInsideImpreseeSearchBar) {
                       _wseeImpreseeSearchBarContainer.classList.add('impresee-hidden-bar');
                    }
                });
            }, 500);
        };
    }
}


documentReady(function(){
    /* search by photo bar */
    var _wseeSearchByPhotoOption = document.createElement('li');
    _wseeSearchByPhotoOption.classList.add('impresee-search-by-photo');
    _wseeSearchByPhotoOption.classList.add('impresee-photo');
    _wseeSearchByPhotoOption.classList.add('impresee-flip-in');
    _wseeSearchByPhotoOption.style.backgroundColor = impreseeBarColor;
    _wseeSearchByPhotoOption.style.color = impreseeBarFontColor;
    _wseeSearchByPhotoOption.style.marginRight = 0;
    var _wseeSearchByPhotoLabel = document.createElement('span');
    _wseeSearchByPhotoLabel.classList.add('impresee-search-label');
    _wseeSearchByPhotoLabel.innerHTML = impreseeVisualSearchLabel;
    var _wseeSearchByPhotoIconContainer = document.createElement('div');
    _wseeSearchByPhotoIconContainer.classList.add('impresee-top-right-radius');
    _wseeSearchByPhotoIconContainer.style.backgroundColor = impreseeIconMainColor;
    var _wseeSearchByPhotoIcon = document.createElement('img');
    _wseeSearchByPhotoIcon.src = iconPhoto;
    _wseeSearchByPhotoIcon.classList.add('impresee-icon');
    _wseeSearchByPhotoIconContainer.appendChild(_wseeSearchByPhotoIcon);
    _wseeSearchByPhotoOption.appendChild(_wseeSearchByPhotoLabel);
    _wseeSearchByPhotoOption.appendChild(_wseeSearchByPhotoIconContainer);

    /* Search by sketch bar */
    var _wseeSearchBySketchOption = document.createElement('li');
    _wseeSearchBySketchOption.classList.add('impresee-search-by-sketch');
    _wseeSearchBySketchOption.classList.add('impresee-sketch');
    _wseeSearchBySketchOption.classList.add('impresee-flip-in');
    _wseeSearchBySketchOption.style.backgroundColor = impreseeBarColor;
    _wseeSearchBySketchOption.style.color = impreseeBarFontColor;
    var _wseeSearchBySketchLabel = document.createElement('span');
    _wseeSearchBySketchLabel.classList.add('impresee-search-label');
    _wseeSearchBySketchLabel.innerHTML = impreseeCreativeSearchLabel;
    var _wseeSearchBySketchIconContainer = document.createElement('div');
    _wseeSearchBySketchIconContainer.classList.add('impresee-bottom-right-radius');
    _wseeSearchBySketchIconContainer.style.backgroundColor = impreseeIconMainColor;
    var _wseeSsearchBySketchIcon = document.createElement('img');
    _wseeSsearchBySketchIcon.src = iconSketch;
    _wseeSsearchBySketchIcon.classList.add('impresee-icon');
    _wseeSearchBySketchIconContainer.appendChild(_wseeSsearchBySketchIcon);
    _wseeSearchBySketchOption.appendChild(_wseeSearchBySketchLabel);
    _wseeSearchBySketchOption.appendChild(_wseeSearchBySketchIconContainer);


    _wseeImpreseeSearchBarContainer.id = 'impresee-search-tab';
    _wseeImpreseeSearchBarContainer.classList.add('impresee-hidden-bar');

    if (typeof _wseeUsePhoto === 'undefined' || _wseeUsePhoto) {
        _wseeImpreseeSearchBar.appendChild(_wseeSearchByPhotoOption);
    }
    if (typeof _wseeUseSketch === 'undefined' || _wseeUseSketch) {
       _wseeImpreseeSearchBar.appendChild(_wseeSearchBySketchOption);
    }
    _wseeImpreseeSearchBarContainer.appendChild(_wseeImpreseeSearchBar);
    var body = document.querySelector('body');
    body.appendChild(_wseeImpreseeSearchBarContainer);
    _wseeAddWidget();
});



window._wseeAddWidget = _wseeAddWidget;


