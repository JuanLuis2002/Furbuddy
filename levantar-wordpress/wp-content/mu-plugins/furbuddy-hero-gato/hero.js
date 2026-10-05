(function () {
	var cfg = window.furbuddyHeroGato;
	if (!cfg || !cfg.media) {
		return;
	}

	var media = cfg.media;
	var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

	function src(name) {
		return media + name;
	}

	function boot() {
		var heading = document.getElementById("el-lugar-favorito-de-tus-mascotas");
		if (!heading) {
			return;
		}
		var columns = heading.closest(".wp-block-stackable-columns");
		if (!columns) {
			return;
		}
		var figure = columns.querySelector(".wp-block-stackable-image figure");
		if (!figure || figure.getAttribute("data-furbuddy-gato") === "1") {
			return;
		}
		figure.setAttribute("data-furbuddy-gato", "1");
		figure.classList.add("furbuddy-hero-gato-host");
		var oldImg = figure.querySelector("img");
		if (oldImg) {
			oldImg.setAttribute("aria-hidden", "true");
		}

		var stage = document.createElement("div");
		stage.className = "furbuddy-hero-stage";

		var cat = document.createElement("img");
		cat.className = "furbuddy-hero-cat";
		cat.alt = "Gato";
		cat.draggable = false;
		cat.src = src("pose-idle.webp");

		var ball = document.createElement("img");
		ball.className = "furbuddy-hero-ball";
		ball.alt = "";
		ball.draggable = false;
		ball.src = src("yarn-ball.webp");

		var sound = document.createElement("button");
		sound.type = "button";
		sound.className = "furbuddy-hero-sound";
		sound.setAttribute("aria-pressed", "false");
		sound.textContent = "Sonido";

		stage.appendChild(cat);
		stage.appendChild(ball);
		stage.appendChild(sound);
		figure.appendChild(stage);

		var pose = "idle";
		var until = 0;
		var nextBlink = performance.now() + 2800;
		var quietSince = performance.now();
		var pointerX = null;
		var audio = null;
		var soundOn = false;
		var dragging = false;

		function show(next) {
			if (pose === next) {
				return;
			}
			pose = next;
			var file = {
				idle: "pose-idle.webp",
				left: "pose-gaze-left.webp",
				right: "pose-gaze-right.webp",
				leap: "pose-leap.webp",
				sleep: "pose-sleep.webp",
				blink: "cat-blink.webp",
				delighted: "cat-delighted.webp"
			}[next];
			cat.src = src(file);
		}

		function tone(freq, seconds) {
			if (!soundOn) {
				return;
			}
			try {
				if (!audio) {
					var Ctx = window.AudioContext || window.webkitAudioContext;
					audio = new Ctx();
				}
				if (audio.state === "suspended") {
					audio.resume();
				}
				var osc = audio.createOscillator();
				var gain = audio.createGain();
				var now = audio.currentTime;
				osc.type = "sine";
				osc.frequency.setValueAtTime(freq, now);
				osc.frequency.exponentialRampToValueAtTime(freq * 1.35, now + seconds);
				gain.gain.setValueAtTime(0.045, now);
				gain.gain.exponentialRampToValueAtTime(0.001, now + seconds);
				osc.connect(gain).connect(audio.destination);
				osc.start();
				osc.stop(now + seconds);
			} catch (err) {
				soundOn = false;
			}
		}

		function setSound(on) {
			soundOn = on;
			sound.setAttribute("aria-pressed", on ? "true" : "false");
			sound.textContent = on ? "Sonido on" : "Sonido";
			if (on) {
				tone(650, 0.11);
			}
		}

		sound.addEventListener("click", function (event) {
			event.preventDefault();
			event.stopPropagation();
			setSound(!soundOn);
		});

		function gaze() {
			if (dragging) {
				var ballBox = ball.getBoundingClientRect();
				var catBox = cat.getBoundingClientRect();
				var ballMid = ballBox.left + ballBox.width / 2;
				var catMid = catBox.left + catBox.width / 2;
				return ballMid < catMid ? "left" : "right";
			}
			if (pointerX === null) {
				return "idle";
			}
			var box = stage.getBoundingClientRect();
			return pointerX < box.left + box.width * 0.42 ? "left" : "right";
		}

		columns.addEventListener("pointermove", function (event) {
			pointerX = event.clientX;
			quietSince = performance.now();
		});
		columns.addEventListener("pointerleave", function () {
			pointerX = null;
			quietSince = performance.now();
		});

		cat.addEventListener("click", function () {
			if (reduced) {
				return;
			}
			quietSince = performance.now();
			until = performance.now() + 700;
			show("delighted");
			tone(510, 0.12);
		});

		ball.addEventListener("pointerdown", function (event) {
			if (reduced) {
				return;
			}
			dragging = true;
			ball.classList.add("is-dragging");
			ball.setPointerCapture(event.pointerId);
			quietSince = performance.now();
		});

		ball.addEventListener("pointermove", function (event) {
			if (!dragging) {
				return;
			}
			var box = stage.getBoundingClientRect();
			var x = ((event.clientX - box.left) / box.width) * 100;
			var y = ((box.bottom - event.clientY) / box.height) * 100;
			x = Math.max(0, Math.min(78, x - 12));
			y = Math.max(0, Math.min(70, y - 8));
			ball.style.left = x + "%";
			ball.style.bottom = y + "%";
		});

		function releaseBall() {
			if (!dragging) {
				return;
			}
			dragging = false;
			ball.classList.remove("is-dragging");
			until = performance.now() + 520;
			show("leap");
			tone(710, 0.15);
			window.setTimeout(function () {
				ball.style.left = "6%";
				ball.style.bottom = "4%";
				tone(390, 0.07);
			}, 280);
		}

		ball.addEventListener("pointerup", releaseBall);
		ball.addEventListener("pointercancel", releaseBall);

		[
			"pose-idle.webp",
			"pose-gaze-left.webp",
			"pose-gaze-right.webp",
			"pose-leap.webp",
			"pose-sleep.webp",
			"cat-blink.webp",
			"cat-delighted.webp"
		].forEach(function (name) {
			var preload = new Image();
			preload.src = src(name);
		});

		if (reduced) {
			return;
		}

		function frame(now) {
			if (now >= until) {
				if (now - quietSince > 9000 && pointerX === null && !dragging) {
					show("sleep");
				} else if (now >= nextBlink) {
					show("blink");
					until = now + 140;
					nextBlink = now + 2600 + Math.random() * 2800;
				} else {
					show(gaze());
				}
			}
			window.requestAnimationFrame(frame);
		}

		window.requestAnimationFrame(frame);
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", boot);
	} else {
		boot();
	}
})();
