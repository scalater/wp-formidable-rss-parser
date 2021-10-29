jQuery(document).ready(function() {

	var podchaseToken = '';
	var podchaseEndpoint = '';
	var lastTypeDate = null;
	var requestInProgress = false;

	jQuery.ajax({
		type: 'POST',
		dataType: 'json',
		url: formidableRSSParserObj.admin_url,
		data: {
			'action': 'get_podchase_token',
			'nonce': formidableRSSParserObj.nonce
		},
		success: function(response) {
			if(response.success){
				podchaseToken = response.data.token;
				podchaseEndpoint = response.data.endpoint;
			}
		},
		error: function(request, status, error) {
			alert(error);
		},
	});

	setInterval(function() {
		if(lastTypeDate != null && podchaseToken != ''){
			let currentDate = new Date();

			if(currentDate - lastTypeDate >= 1000){

				let search = jQuery(".formidable-rss-parser").val();

				if(search.length >= 3 && !search.startsWith('http')) {

					lastTypeDate = null;

					if(!requestInProgress){

						const searchButton = jQuery('.formidable-rss-parser-container-shortcode .search-show');

						formidableRSSParserInstance.shortCodeLoadingAdd(searchButton);

						jQuery.ajax({
							method: "POST",
							url: podchaseEndpoint,
							contentType: "application/json",
							headers: {
								Authorization: podchaseToken
							},
							data: JSON.stringify({
								query: `query{
									podcasts(
										filters: {
											rating: {
												minRating: 1
												maxRating: 5
											}
										}
										first: 10
										page: 0
										searchTerm: "`+search+`"
									){
										paginatorInfo{
											count
											currentPage
											firstItem
											hasMorePages
											lastItem
											lastPage
											perPage
											total
										},
										data{
											id,
											title,
											rssUrl,
											imageUrl,
											author{
												email,
												name
											}
										}
									}
								}`
							}),
							success: function(response) {
								proccessPodchaseResponse(response);
							},
							error: function(request, status, error) {
								// alert(error);
							},
							complete: function() {
								formidableRSSParserInstance.shortCodeLoadingRemove(searchButton);
							},
						});
					}
				}else{
					if(search == "") {

						lastTypeDate = null

						//clean and hide result board
					}
				}
			}
		}
	}, 500);

	jQuery('.formidable-rss-parser').on('keyup', function (e){
		if(e.keyCode != 13){
			lastTypeDate = new Date();
		}
	});

	function proccessPodchaseResponse(response){
		var searchResultHTML = '';

		if(response.data.podcasts.paginatorInfo.count > 0){
			for(let i = 0; i < response.data.podcasts.paginatorInfo.count; i++){
				searchResultHTML += getShowContainer(response.data.podcasts.data[i]);
			}
		}

		jQuery('.formidable-rss-result-show').html(searchResultHTML);

		if(searchResultHTML != ''){
			jQuery('.formidable-rss-result-show').show();
		}else{
			jQuery('.formidable-rss-result-show').hide();
		}
	}

	function getShowContainer(podcast){
		return '<label class="element-list search-result-show-item" onclick="replaceRSSUrl(\''+podcast.rssUrl+'\');">' +
			'<div class="element-image">' +
			'<img src="' + podcast.imageUrl + '" alt="' + podcast.title + '">' +
			'</div>' +
			'<div class="element-details">' +
			'<div class="element-title">' + podcast.title + '</div>' +
			'<div class="element-sub-details">' +
			'<span class="element-author">' + podcast.author.name + '</span>' +
			'<span class="element-separator">&centerdot;</span>' +
			'<span class="element-episode-amount">' + 10 + '</span>' +
			'</div>' +
			'</div>' +
			'</label>';
	}

});

function replaceRSSUrl(rssUrl){
	jQuery(".formidable-rss-parser").val(rssUrl);
}
