/* The original artwork is kept intact. Matching renders replace only local
   facial regions so the fur and silhouette remain continuous. */
window.createFurbuddyEyeRig = function (canvas, images) {
  const gl = canvas.getContext('webgl2', {
    alpha: true, antialias: true, depth: false, stencil: false,
    premultipliedAlpha: true, preserveDrawingBuffer: false,
  });
  if (!gl) return null;

  const vertexSource = `#version 300 es
    in vec2 aPosition;
    out vec2 vUv;
    void main() {
      vUv = (aPosition + 1.0) * 0.5;
      gl_Position = vec4(aPosition, 0.0, 1.0);
    }`;
  const fragmentSource = `#version 300 es
    precision highp float;
    in vec2 vUv;
    out vec4 fragColor;
    uniform sampler2D uOpen;
    uniform sampler2D uBlank;
    uniform sampler2D uClosed;
    uniform sampler2D uDelighted;
    uniform vec2 uLook;
    uniform vec2 uHead;
    uniform vec2 uEars;
    uniform float uTail;
    uniform float uBlink;
    uniform float uDelight;
    const vec2 SIZE = vec2(1199.0, 1312.0);

    float oval(vec2 p, vec2 center, vec2 radius, float feather) {
      float distanceFromCenter = length((p - center) / radius);
      return 1.0 - smoothstep(1.0 - feather, 1.0, distanceFromCenter);
    }

    float irisAt(vec2 sourcePoint, vec2 center, vec2 radius,
                 vec4 openPixel, vec4 blankPixel) {
      float identityDifference = length(openPixel.rgb - blankPixel.rgb);
      float footprint = oval(sourcePoint, center, radius, 0.24);
      return footprint * smoothstep(0.28, 0.58, identityDifference);
    }

    void main() {
      vec2 outputPoint = vec2(vUv.x * SIZE.x, (1.0 - vUv.y) * SIZE.y);
      float headWeight = 1.0 - smoothstep(620.0, 1110.0, outputPoint.y);
      float leftEar = oval(outputPoint, vec2(355.0, 318.0), vec2(220.0, 260.0), 0.42)
        * (1.0 - smoothstep(415.0, 525.0, outputPoint.y));
      float rightEar = oval(outputPoint, vec2(861.0, 155.0), vec2(216.0, 262.0), 0.42)
        * (1.0 - smoothstep(275.0, 390.0, outputPoint.y));
      float tailTip = oval(outputPoint, vec2(171.0, 695.0), vec2(232.0, 269.0), 0.40)
        * (1.0 - smoothstep(295.0, 427.0, outputPoint.x));
      vec2 displacement = uHead * vec2(13.0, 8.0) * headWeight
        + vec2(uEars.x * 12.0, -abs(uEars.x) * 5.0) * leftEar
        + vec2(uEars.y * 13.0, -abs(uEars.y) * 6.0) * rightEar
        + vec2(uTail * 7.0, uTail * 21.0) * tailTip;
      vec2 point = outputPoint - displacement;
      vec2 sampleUv = vec2(point.x / SIZE.x, 1.0 - point.y / SIZE.y);
      vec4 openPixel = texture(uOpen, sampleUv);
      vec4 blankPixel = texture(uBlank, sampleUv);

      // Each socket is bounded separately, so the pupil can move without
      // painting over the fur, brow or lower lid.
      float leftSocket = oval(point, vec2(588.0, 558.0), vec2(155.0, 125.0), 0.16);
      float rightSocket = oval(point, vec2(925.0, 450.0), vec2(112.0, 134.0), 0.18);
      float socket = max(leftSocket, rightSocket);
      vec4 color = mix(openPixel, blankPixel, socket);

      vec2 offset = uLook * vec2(16.0, 11.0);
      vec2 sourcePoint = point - offset;
      vec2 sourceUv = vec2(sourcePoint.x / SIZE.x, 1.0 - sourcePoint.y / SIZE.y);
      vec4 openSource = texture(uOpen, sourceUv);
      vec4 blankSource = texture(uBlank, sourceUv);
      float leftIris = irisAt(sourcePoint, vec2(568.0, 561.0), vec2(93.0, 88.0), openSource, blankSource) * leftSocket;
      float rightIris = irisAt(sourcePoint, vec2(912.0, 464.0), vec2(56.0, 82.0), openSource, blankSource) * rightSocket;
      color = mix(color, openSource, max(leftIris, rightIris));

      // At rest show the untouched master image, not a reconstruction.
      float lookAmount = smoothstep(0.0, 0.055, length(uLook));
      color = mix(openPixel, color, lookAmount);
      vec4 closedPixel = texture(uClosed, sampleUv);
      color = mix(color, closedPixel, uBlink);
      vec4 delightedPixel = texture(uDelighted, sampleUv);
      float mouth = oval(point, vec2(815.0, 630.0), vec2(137.0, 91.0), 0.34);
      color = mix(color, delightedPixel, mouth * uDelight);
      fragColor = vec4(color.rgb, mix(openPixel.a, closedPixel.a, uBlink));
    }`;

  function compile(type, source) {
    const shader = gl.createShader(type);
    gl.shaderSource(shader, source);
    gl.compileShader(shader);
    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
      console.error('Miso eye shader failed:', gl.getShaderInfoLog(shader));
      gl.deleteShader(shader);
      return null;
    }
    return shader;
  }

  const vertexShader = compile(gl.VERTEX_SHADER, vertexSource);
  const fragmentShader = compile(gl.FRAGMENT_SHADER, fragmentSource);
  if (!vertexShader || !fragmentShader) return null;
  const program = gl.createProgram();
  gl.attachShader(program, vertexShader);
  gl.attachShader(program, fragmentShader);
  gl.linkProgram(program);
  if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
    console.error('Miso eye shader link failed:', gl.getProgramInfoLog(program));
    return null;
  }

  const positions = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, positions);
  gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]), gl.STATIC_DRAW);
  const positionLocation = gl.getAttribLocation(program, 'aPosition');
  gl.enableVertexAttribArray(positionLocation);
  gl.vertexAttribPointer(positionLocation, 2, gl.FLOAT, false, 0, 0);
  gl.useProgram(program);
  gl.viewport(0, 0, canvas.width, canvas.height);
  gl.uniform1i(gl.getUniformLocation(program, 'uOpen'), 0);
  gl.uniform1i(gl.getUniformLocation(program, 'uBlank'), 1);
  gl.uniform1i(gl.getUniformLocation(program, 'uClosed'), 2);
  gl.uniform1i(gl.getUniformLocation(program, 'uDelighted'), 3);
  const lookLocation = gl.getUniformLocation(program, 'uLook');
  const headLocation = gl.getUniformLocation(program, 'uHead');
  const earsLocation = gl.getUniformLocation(program, 'uEars');
  const tailLocation = gl.getUniformLocation(program, 'uTail');
  const blinkLocation = gl.getUniformLocation(program, 'uBlink');
  const delightLocation = gl.getUniformLocation(program, 'uDelight');
  let ready = false;
  let lost = false;
  canvas.addEventListener('webglcontextlost', event => { event.preventDefault(); lost = true; canvas.classList.remove('is-ready'); });

  function resizeResolution() {
    const sourceWidth = 1199; const sourceHeight = 1312;
    const displayWidth = Math.min(canvas.clientWidth, canvas.clientHeight * sourceWidth / sourceHeight);
    if (!displayWidth) return;
    const width = Math.min(sourceWidth, Math.max(320, Math.round(displayWidth * Math.min(devicePixelRatio || 1, 2))));
    const height = Math.round(width * sourceHeight / sourceWidth);
    if (canvas.width === width && canvas.height === height) return;
    canvas.width = width;
    canvas.height = height;
    gl.viewport(0, 0, width, height);
  }
  new ResizeObserver(resizeResolution).observe(canvas);
  window.addEventListener('resize', resizeResolution, { passive: true });

  const loaded = Promise.all(images.map(image => image.complete && image.naturalWidth ? Promise.resolve() : image.decode()))
    .then(() => {
      gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
      gl.pixelStorei(gl.UNPACK_PREMULTIPLY_ALPHA_WEBGL, true);
      for (let i = 0; i < images.length; i++) {
        const texture = gl.createTexture();
        gl.activeTexture(gl.TEXTURE0 + i);
        gl.bindTexture(gl.TEXTURE_2D, texture);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
        gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, images[i]);
      }
      ready = true;
      resizeResolution();
      draw(0, 0, 0, 0, 0, 0, 0);
      canvas.classList.add('is-ready');
      return true;
    })
    .catch(error => { console.error('Miso eye assets failed:', error); return false; });

  function draw(x, y, blink, delight, leftEar, rightEar, tail) {
    if (!ready || lost) return;
    gl.useProgram(program);
    gl.uniform2f(lookLocation, Math.max(-1, Math.min(1, x)), Math.max(-1, Math.min(1, y)));
    gl.uniform2f(headLocation, Math.max(-1, Math.min(1, x * .72)), Math.max(-1, Math.min(1, y * .55)));
    gl.uniform2f(earsLocation, leftEar, rightEar);
    gl.uniform1f(tailLocation, tail);
    gl.uniform1f(blinkLocation, Math.max(0, Math.min(1, blink)));
    gl.uniform1f(delightLocation, Math.max(0, Math.min(1, delight)));
    gl.clearColor(0, 0, 0, 0);
    gl.clear(gl.COLOR_BUFFER_BIT);
    gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
  }
  return { loaded, draw };
};
