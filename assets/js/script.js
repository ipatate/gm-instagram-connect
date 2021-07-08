document.addEventListener("DOMContentLoaded", function () {
	var video = document.querySelectorAll(".gm-instagram-video > video");
	video.forEach((v) => {
		var button = v.parentNode.querySelector("button");
		var videoPlayer = v;
		button.addEventListener("click", function (e) {
			e.preventDefault();
			if (videoPlayer.paused) {
				videoPlayer.play();
				videoPlayer.loop = true;
				e.currentTarget.classList.remove("gm-instagram-video-paused");
				e.currentTarget.classList.add("gm-instagram-video-played");
			} else {
				videoPlayer.pause();
				videoPlayer.loop = false;
				e.currentTarget.classList.add("gm-instagram-video-paused");
				e.currentTarget.classList.remove("gm-instagram-video-played");
			}
		});
	});
});
