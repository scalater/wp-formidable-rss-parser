jQuery(document).ready(function() {

	var lastTypeDate = null;
	var requestInProgress = false;

	setInterval(function() {
		if(lastTypeDate != null){
			let currentDate = new Date();

			if(currentDate - lastTypeDate >= 2000){

				let search = jQuery(".formidable-rss-parser").val();

				if(search.length >= 3 && !search.startsWith('http')) {

					lastTypeDate = null;

					if(!requestInProgress){

						const searchButton = jQuery('.formidable-rss-parser-container-shortcode .search-show');

						formidableRSSParserInstance.shortCodeLoadingAdd(searchButton);

						jQuery.ajax({
							method: "POST",
							url: "https://api.podchaser.com/graphql",
							contentType: "application/json",
							headers: {
								Authorization: "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI5NGFlNjc3Yy1kYWNjLTQ5ZTctYjdjZC1kZWFhZWZlZjdmMTMiLCJqdGkiOiI3YTBkZGQxZjE3Nzc2MjQ4OWQ2NjQzNzdkNzYwOGMzOTgyZGY1YmFkOTU2N2ZiNWE1ZTllOGY0Y2YzYTA3NTU5ZTg5YWE1YjA5NTNiOTIyOCIsImlhdCI6MTYzNDkyMjU2Mi40MjI5MiwibmJmIjoxNjM0OTIyNTYyLjQyMjkyNSwiZXhwIjoxNjY2NDU4NTYyLjQwNTA3LCJzdWIiOiIiLCJzY29wZXMiOlsiKiJdfQ.SvCbEh4cJ2VP2ktbAPqMWuKaPdY0-aSh22oyugRPvQtjUxoEvgdhINv8WlhJIWZVApQ3ujsE3_ZS6emQGxLeeJ2srNWHDR3aNdIaO1KKXKoNC5hX8jKKSrqcYWheEqfKG4hwUbyVsl9k1k1_Pv7sxjmPeISG8bFQtrXPwsbnmIZ6p0EgEgdAQZOokAEvgvGEw68qNWotwHhbH4dCfdutY3E-AY_Y7DwAhK90sVRh6GZkFhSio-SEUUCtdnqj5jMXVENty4MkdqBQelP8DA65oolHVWpP8wzoRPXufVG5J4S4aTS-NfFl__Y_bEJd-8mMZpYRthi0ZG4KIcrSX_ccD26Wa8IyqwKwNQVnhHiTG0GR2jhtearpGd-S_pCdTc_Nv__zJKe4RTsOrNG_SRtrye0R8qNyKBjQRN86DDDO5EOQ6BwoaOBnAttMu3mVRdKa5d88Ol5oSCm8XX7iUi_zZFJFCWqFMV_wWzNNYffoNpmnOliNrH3h7sbu69XEF4aanU4Eq6FfxubIlscQxJ4J3I0AGACi1EAH4gKKQGolrk1yfFe6ba-KqL21vGCqfM2SdvuULOtJNjmjSUV16CmwkQB65QEbNZ6znjtAhsN5er5LPywGGsJbnxVfaomLKiXynSoWrTItGCQSau2NQD4Jx1Y9JfICvpRQeFBXuNbFj1M"
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
	}, 1000);

	jQuery('.formidable-rss-parser').on('keyup', function (e){
		if(e.keyCode != 13){
			lastTypeDate = new Date();
		}
	});

	//TODO: remove if don't needed
	jQuery('.search-show').on('click', function(e){
		// requestInProgress = true;

		// e.preventDefault();
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
		return '<label class="element-list search-result-show-item" onclick="searchRSS(\''+podcast.rssUrl+'\');">' +
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

function searchRSS(rssUrl){
	jQuery(".formidable-rss-parser").val(rssUrl);
	// jQuery('.formidable-rss-result-show').html('');
	// jQuery('.search-show').click();

	formidableRSSParserInstance.setShowData();
}
