(function ($) {
	"use strict";

	// Spinner
	let spinner = function () {
		setTimeout(function () {
			if ($("#spinner").length > 0) {
				$("#spinner").removeClass("show");
			}
		}, 1);
	};
	spinner();

	// Initiate the wowjs
	new WOW().init();

	// Sticky Navbar
	$(window).scroll(function () {
		if ($(this).scrollTop() > 300) {
			$(".sticky-top").addClass("shadow-sm").css("top", "0px");
		} else {
			$(".sticky-top").removeClass("shadow-sm").css("top", "-100px");
		}
	});

	// Back to top button
	$(window).scroll(function () {
		if ($(this).scrollTop() > 300) {
			$(".back-to-top").fadeIn("slow");
		} else {
			$(".back-to-top").fadeOut("slow");
		}
	});
	$(".back-to-top").click(function () {
		$("html, body").animate({ scrollTop: 0 }, 1500, "easeInOutExpo");
		return false;
	});

	// Testimonials carousel
	$(".testimonial-carousel").owlCarousel({
		autoplay: true,
		smartSpeed: 1000,
		loop: true,
		nav: false,
		dots: true,
		items: 1,
		dotsData: true,
	});

	// PHP form
	$("#send-message").submit(function (event) {
		event.preventDefault();
		let form = $(this);

		$.ajax({
			type: form.attr("method"),
			url: form.prop("action"),
			data: form.serialize(),
			dataType: "json",
			success: function (response) {
				$("#message-content").text(response.message);
				$("#send-message").fadeOut(1000);
			},
			error: function (response) {
				$("#message-content").text(response.responseJSON.message ?? "Não conseguimos receber sua mensagem. Tente novamente mais tarde.");
			},
		});
	});

	// Load all downloads
	$.ajax({
		type: "get",
		url: document.baseURI + "files.php",
		dataType: "json",
		success: function (response) {
			let downloadElement = $("#download-list");
			let elementContent = $("#template-download").html();

			response.forEach((element) => {
				downloadElement.append(
					elementContent
						.replaceAll("{{FILENAME}}", element.filename)
						.replace("{{DATE}}", element.datetime)
						.replace("{{NEW}}", element.current ? "NOVO" : "")
						.replace("{{LINK}}", element.directory + "/" + element.filename)
				);
			});

			// Links fade up
			$(".fadeInUp").each(function () {
				$(this).attr("data-wow-delay", "0." + (Math.floor(Math.random() * 3) + 1) + "s");
			});
		},
	});
})(jQuery);
