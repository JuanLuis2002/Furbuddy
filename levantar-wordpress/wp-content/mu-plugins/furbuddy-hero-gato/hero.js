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

		var portal = document.createElement("div");
		portal.className = "furbuddy-portal";
		portal.setAttribute("aria-hidden", "true");
		portal.innerHTML = '<span class="furbuddy-ring furbuddy-ring-one"></span><span class="furbuddy-ring furbuddy-ring-two"></span><span class="furbuddy-portal-core"></span>';

		var orbitOne = document.createElement("span");
		orbitOne.className = "furbuddy-orbit furbuddy-orbit-one";
		orbitOne.setAttribute("aria-hidden", "true");
		orbitOne.textContent = "✳";

		var orbitTwo = document.createElement("span");
		orbitTwo.className = "furbuddy-orbit furbuddy-orbit-two";
		orbitTwo.setAttribute("aria-hidden", "true");
		orbitTwo.textContent = "✦";

		var cat = document.createElement("img");
		cat.className = "furbuddy-hero-cat";
		cat.alt = "Gato";
		cat.draggable = false;
		cat.src = src("pose-idle.webp");

		var thought = document.createElement("div");
		thought.className = "furbuddy-thought";
		thought.textContent = "oh, hi you.";

		var particles = document.createElement("div");
		particles.className = "furbuddy-particles";
		particles.setAttribute("aria-hidden", "true");

		var thread = document.createElementNS("http://www.w3.org/2000/svg", "svg");
		thread.setAttribute("class", "furbuddy-thread");
		thread.setAttribute("aria-hidden", "true");
		var path = document.createElementNS("http://www.w3.org/2000/svg", "path");
		thread.appendChild(path);

		var ball = document.createElement("img");
		ball.className = "furbuddy-hero-ball";
		ball.alt = "";
		ball.draggable = false;
		ball.src = src("yarn-ball.webp");

		var tip = document.createElement("button");
		tip.type = "button";
		tip.className = "furbuddy-thread-tip";
		tip.setAttribute("aria-label", "Jala el lazo de la pelota");

		var sound = document.createElement("button");
		sound.type = "button";
		sound.className = "furbuddy-hero-sound";
		sound.setAttribute("aria-pressed", "false");
		sound.textContent = "Sonido";

		stage.appendChild(portal);
		stage.appendChild(orbitOne);
		stage.appendChild(orbitTwo);
		stage.appendChild(cat);
		var eyeCanvas = document.createElement("canvas");
		eyeCanvas.className = "furbuddy-eye-canvas";
		eyeCanvas.width = 1199;
		eyeCanvas.height = 1312;
		eyeCanvas.setAttribute("aria-hidden", "true");
		stage.appendChild(eyeCanvas);
		stage.appendChild(thought);
		stage.appendChild(particles);
		stage.appendChild(thread);
		stage.appendChild(ball);
		stage.appendChild(tip);
		stage.appendChild(sound);
		figure.appendChild(stage);

		if (window.createFurbuddyEyeRig) {
			var openEyes = new Image();
			var blankEyes = new Image();
			var closedEyes = new Image();
			var delightedFace = new Image();
			openEyes.src = src("pose-idle.webp");
			blankEyes.src = src("cat-blank-eyes.webp");
			closedEyes.src = src("cat-blink.webp");
			delightedFace.src = src("cat-delighted.webp");
			eyeRig = window.createFurbuddyEyeRig(eyeCanvas, [openEyes, blankEyes, closedEyes, delightedFace]);
			if (eyeRig) {
				eyeRig.loaded.then(function (ok) {
					if (ok) {
						stage.classList.add("has-eyes");
					}
				});
			}
		}

		var pose = "idle";
		var until = 0;
		var nextBlink = performance.now() + 2800;
		var quietSince = performance.now();
		var pointerX = null;
		var pointerY = null;
		var pointerAt = 0;
		var audio = null;
		var soundOn = false;
		var draggingBall = false;
		var draggingTip = false;
		var lookX = 0;
		var lookY = 0;
		var blinkAt = 0;
		var nextEyeBlink = performance.now() + 1800;
		var delight = 0;
		var delightUntil = 0;
		var leftEar = 0;
		var leftEarV = 0;
		var rightEar = 0;
		var rightEarV = 0;
		var tail = 0;
		var tailV = 0;
		var eyeRig = null;
		var tipX = 0;
		var tipY = 0;
		var tipVx = 0;
		var tipVy = 0;
		var thoughtTimer = 0;
		var toy = { x: 0, y: 0, vx: 0, vy: 0, angle: 0, angleV: 0, ready: false, lastAt: 0 };

		function show(next) {
			if (pose === next) {
				return;
			}
			pose = next;
			stage.classList.toggle("is-asleep", next === "sleep");
			var eyes = stage.classList.contains("has-eyes");
			var facial = next === "idle" || next === "left" || next === "right" || next === "blink";
			var file = {
				idle: "pose-idle.webp",
				left: "pose-gaze-left.webp",
				right: "pose-gaze-right.webp",
				leap: "pose-leap.webp",
				sleep: "pose-sleep.webp",
				blink: "cat-blink.webp",
				delighted: "cat-delighted.webp"
			}[next];
			if (!(eyes && facial)) {
				cat.src = src(file);
			}
			if (next === "sleep") {
				thought.classList.add("is-hidden");
			}
		}

		function say(message) {
			thought.textContent = message;
			thought.classList.remove("is-hidden");
			window.clearTimeout(thoughtTimer);
			thoughtTimer = window.setTimeout(function () {
				if (pose !== "sleep") {
					thought.textContent = "oh, hi you.";
				}
			}, 2500);
		}

		function burst() {
			if (reduced) {
				return;
			}
			for (var i = 0; i < 10; i++) {
				var part = document.createElement("span");
				part.className = "furbuddy-particle";
				part.textContent = "✳";
				var angle = Math.PI * 2 * i / 10;
				part.style.setProperty("--dx", Math.cos(angle) * (70 + Math.random() * 60) + "px");
				part.style.setProperty("--dy", Math.sin(angle) * (50 + Math.random() * 50) + "px");
				part.style.setProperty("--rotation", Math.round(Math.random() * 100 - 50) + "deg");
				part.style.animationDelay = i * 17 + "ms";
				particles.appendChild(part);
				window.setTimeout(function (node) {
					node.remove();
				}, 1100, part);
			}
		}

		function leap(message) {
			quietSince = performance.now();
			until = performance.now() + (reduced ? 150 : 780);
			stage.classList.add("is-leaping");
			show("leap");
			burst();
			say(message);
			delightUntil = performance.now() + 2600;
			tone(710, 0.15);
			window.setTimeout(function () {
				stage.classList.remove("is-leaping");
			}, reduced ? 150 : 780);
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

		function clamp(value, min, max) {
			return Math.max(min, Math.min(max, value));
		}

		function ballBounds() {
			var width = stage.clientWidth;
			var height = stage.clientHeight;
			var radius = ball.offsetWidth / 2 || 24;
			return {
				radius: radius,
				minX: radius,
				maxX: Math.max(radius, width - radius),
				minY: radius,
				maxY: Math.max(radius, height - radius),
				floorY: Math.max(radius, height * 0.9 - radius)
			};
		}

		function ballAttachment() {
			var angle = toy.angle * Math.PI / 180;
			var offset = -(ball.offsetWidth || 48) * 0.28;
			return {
				x: toy.x + offset * Math.cos(angle) - offset * Math.sin(angle),
				y: toy.y + offset * Math.sin(angle) + offset * Math.cos(angle)
			};
		}

		function positionBall() {
			ball.style.left = toy.x + "px";
			ball.style.top = toy.y + "px";
			ball.style.transform = "translate(-50%, -50%) rotate(" + toy.angle.toFixed(1) + "deg)";
		}

		function placeThread() {
			if (!stage.clientWidth) {
				return;
			}
			var attach = ballAttachment();
			var cx = (attach.x + tipX) / 2;
			var cy = (attach.y + tipY) / 2 + Math.min(28, 7 + Math.abs(tipX - attach.x) * 0.06);
			path.setAttribute("d", "M " + attach.x.toFixed(1) + " " + attach.y.toFixed(1) + " Q " + cx.toFixed(1) + " " + cy.toFixed(1) + " " + tipX.toFixed(1) + " " + tipY.toFixed(1));
			tip.style.left = tipX + "px";
			tip.style.top = tipY + "px";
		}

		function resetBall() {
			var bounds = ballBounds();
			if (!stage.clientWidth || bounds.maxX <= bounds.minX) {
				return;
			}
			toy.x = stage.clientWidth * 0.22;
			toy.y = Math.min(bounds.floorY, stage.clientHeight * 0.76);
			toy.vx = 0;
			toy.vy = 0;
			toy.angle = 0;
			toy.angleV = 0;
			toy.ready = true;
			var attach = ballAttachment();
			tipX = attach.x - 43;
			tipY = attach.y - 27;
			tipVx = 0;
			tipVy = 0;
			positionBall();
			placeThread();
		}

		function stepToy(dt) {
			if (reduced || draggingBall || !toy.ready) {
				return;
			}
			var bounds = ballBounds();
			toy.vy += 1050 * dt;
			toy.x += toy.vx * dt;
			toy.y += toy.vy * dt;
			if (toy.x < bounds.minX || toy.x > bounds.maxX) {
				toy.x = clamp(toy.x, bounds.minX, bounds.maxX);
				toy.vx *= -0.65;
				toy.angleV *= -0.52;
			}
			if (toy.y > bounds.floorY) {
				toy.y = bounds.floorY;
				toy.vy *= -0.55;
				toy.vx *= 0.78;
				toy.angleV = toy.vx / Math.max(1, bounds.radius) * 180 / Math.PI;
				if (Math.abs(toy.vy) < 27) {
					toy.vy = 0;
				}
			}
			if (toy.y < bounds.minY) {
				toy.y = bounds.minY;
				toy.vy *= -0.6;
			}
			toy.angle += toy.angleV * dt;
			toy.angleV *= Math.exp(-1.1 * dt);
			toy.vx *= Math.exp(-0.75 * dt);
			positionBall();
			if (!draggingTip) {
				var attach = ballAttachment();
				var targetX = attach.x - 43;
				var targetY = attach.y - 27;
				tipVx = (tipVx + (targetX - tipX) * 75 * dt) * Math.exp(-12 * dt);
				tipVy = (tipVy + (targetY - tipY) * 75 * dt) * Math.exp(-12 * dt);
				tipX += tipVx * dt;
				tipY += tipVy * dt;
			}
			placeThread();
		}

		sound.addEventListener("click", function (event) {
			event.preventDefault();
			event.stopPropagation();
			soundOn = !soundOn;
			sound.setAttribute("aria-pressed", soundOn ? "true" : "false");
			sound.textContent = soundOn ? "Sonido on" : "Sonido";
			if (soundOn) {
				tone(650, 0.11);
			}
		});

		function gaze() {
			if (draggingBall || draggingTip) {
				var ballBox = ball.getBoundingClientRect();
				var catBox = cat.getBoundingClientRect();
				return ballBox.left + ballBox.width / 2 < catBox.left + catBox.width / 2 ? "left" : "right";
			}
			if (pointerX === null) {
				return "idle";
			}
			var box = stage.getBoundingClientRect();
			return pointerX < box.left + box.width * 0.42 ? "left" : "right";
		}

		columns.addEventListener("pointermove", function (event) {
			pointerX = event.clientX;
			pointerY = event.clientY;
			pointerAt = performance.now();
			quietSince = pointerAt;
			if (pose === "sleep") {
				say("oh, hi you.");
			}
		});
		columns.addEventListener("pointerleave", function () {
			quietSince = performance.now();
		});

		cat.addEventListener("click", function (event) {
			event.stopPropagation();
			leap("hey, that tickles!");
		});

		ball.addEventListener("pointerdown", function (event) {
			draggingBall = true;
			toy.vx = 0;
			toy.vy = 0;
			toy.lastAt = performance.now();
			ball.classList.add("is-dragging");
			ball.setPointerCapture(event.pointerId);
			quietSince = performance.now();
			event.stopPropagation();
		});
		ball.addEventListener("pointermove", function (event) {
			if (!draggingBall) {
				return;
			}
			var box = stage.getBoundingClientRect();
			var bounds = ballBounds();
			var x = clamp(event.clientX - box.left, bounds.minX, bounds.maxX);
			var y = clamp(event.clientY - box.top, bounds.minY, bounds.maxY);
			var now = performance.now();
			var dt = Math.max(0.008, (now - toy.lastAt) / 1000);
			toy.vx = clamp((x - toy.x) / dt, -1000, 1000);
			toy.vy = clamp((y - toy.y) / dt, -1000, 1000);
			toy.angle += (x - toy.x) * 0.82;
			toy.angleV = toy.vx * 0.55;
			toy.x = x;
			toy.y = y;
			toy.lastAt = now;
			positionBall();
			placeThread();
			event.stopPropagation();
		});
		function releaseBall() {
			if (!draggingBall) {
				return;
			}
			draggingBall = false;
			ball.classList.remove("is-dragging");
			leap("mine now!");
		}
		ball.addEventListener("pointerup", releaseBall);
		ball.addEventListener("pointercancel", releaseBall);

		tip.addEventListener("pointerdown", function (event) {
			draggingTip = true;
			tipVx = 0;
			tipVy = 0;
			tip.setPointerCapture(event.pointerId);
			event.stopPropagation();
		});
		tip.addEventListener("pointermove", function (event) {
			if (!draggingTip) {
				return;
			}
			var box = stage.getBoundingClientRect();
			tipX = clamp(event.clientX - box.left, 8, box.width - 8);
			tipY = clamp(event.clientY - box.top, 8, box.height - 8);
			placeThread();
			event.stopPropagation();
		});
		function releaseTip() {
			if (!draggingTip) {
				return;
			}
			draggingTip = false;
			toy.vx += clamp((tipX - toy.x) * 4.4, -940, 940);
			toy.vy += clamp((tipY - toy.y) * 4.4 - 180, -900, 400);
			toy.angleV = toy.vx * 0.75;
			leap("got it!");
		}
		tip.addEventListener("pointerup", releaseTip);
		tip.addEventListener("pointercancel", function () {
			draggingTip = false;
		});

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

		window.addEventListener("resize", function () {
			if (!toy.ready) {
				resetBall();
				return;
			}
			var bounds = ballBounds();
			toy.x = clamp(toy.x, bounds.minX, bounds.maxX);
			toy.y = clamp(toy.y, bounds.minY, bounds.floorY);
			positionBall();
			placeThread();
		});
		ball.addEventListener("load", resetBall);
		window.requestAnimationFrame(resetBall);

		function updateEyes(now, dt) {
			if (!eyeRig || !stage.classList.contains("has-eyes")) {
				return;
			}
			var box = stage.getBoundingClientRect();
			var active = pointerX !== null && now - pointerAt < 4000 && box.width;
			var driftX = active ? ((pointerX - box.left) / box.width - 0.5) * 2 : Math.sin(now / 2250) * 0.35;
			var driftY = active ? ((pointerY - box.top) / box.height - 0.5) * 2 : Math.sin(now / 3050) * 0.22;
			var chase = draggingBall || draggingTip;
			var lookTargetX = chase ? (toy.x / Math.max(1, stage.clientWidth) - 0.5) * 2 : driftX;
			var lookTargetY = chase ? (toy.y / Math.max(1, stage.clientHeight) - 0.5) * 2 : driftY;
			var gazeStep = reduced ? 1 : 1 - Math.exp(-16 * dt);
			lookX += (lookTargetX - lookX) * gazeStep;
			lookY += (lookTargetY - lookY) * gazeStep;
			if (now >= nextEyeBlink) {
				blinkAt = now;
				nextEyeBlink = now + 2400 + Math.random() * 3200;
			}
			var blinkProgress = (now - blinkAt) / 190;
			var blink = reduced || blinkProgress < 0 || blinkProgress > 1 ? 0 : Math.pow(Math.sin(Math.PI * blinkProgress), 2);
			var wantDelight = draggingBall || now < delightUntil;
			delight += ((wantDelight ? 1 : 0) - delight) * (reduced ? 1 : 1 - Math.exp(-7 * dt));
			function springPart(value, velocity, target, stiffness, damping) {
				velocity += (target - value) * stiffness * dt;
				velocity *= Math.exp(-damping * dt);
				return [value + velocity * dt, velocity];
			}
			var left = springPart(leftEar, leftEarV, -lookX * 0.4 + Math.sin(now / 1150) * 0.08, 95, 11);
			leftEar = left[0];
			leftEarV = left[1];
			var right = springPart(rightEar, rightEarV, lookX * 0.35 + Math.sin(now / 1350 + 1) * 0.07, 88, 10);
			rightEar = right[0];
			rightEarV = right[1];
			var tailPart = springPart(tail, tailV, Math.sin(now / 480) * 0.55 + lookX * 0.22, 33, 6);
			tail = tailPart[0];
			tailV = tailPart[1];
			if (pose !== "leap" && pose !== "sleep" && pose !== "delighted") {
				eyeRig.draw(lookX, lookY, blink, delight, leftEar, rightEar, tail);
			}
		}

		var lastStep = performance.now();

		function frame(now) {
			var dt = Math.min(0.033, Math.max(0.001, (now - lastStep) / 1000));
			lastStep = now;
			stepToy(dt);
			var eyesOn = stage.classList.contains("has-eyes");
			if (!reduced && now >= until) {
				if (now - quietSince > 9000 && pointerX === null && !draggingBall && !draggingTip) {
					show("sleep");
				} else if (!eyesOn && now >= nextBlink) {
					show("blink");
					until = now + 140;
					nextBlink = now + 2600 + Math.random() * 2800;
				} else {
					show(eyesOn ? "idle" : gaze());
				}
			}
			updateEyes(now, dt);
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
