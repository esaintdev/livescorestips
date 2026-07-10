// Immediately insert popup banner at bottom right
(function () {
  // Inject style
  const style = document.createElement("style");
  style.innerHTML = `
    #image-popup-banner {
      display: block;
      position: fixed;
      bottom: 100px;
      right: 20px;
      width: 280px;
      height: 233px;
      z-index: 9999;
      box-shadow: 0 0 12px rgba(0,0,0,0.4);
      background: white;
      border-radius: 10px;
    }

    #image-popup-banner img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 10px;
      cursor: pointer;
      display: block;
    }

    #close-image-popup {
      position: absolute;
      top: 5px;
      right: 5px;
      background: rgba(0,0,0,0.6);
      color: white;
      border: none;
      border-radius: 50%;
      width: 24px;
      height: 24px;
      font-size: 14px;
      line-height: 24px;
      text-align: center;
      cursor: pointer;
    }
  `;
  document.head.appendChild(style);

  // Insert popup directly
  const div = document.createElement("div");
  div.id = "image-popup-banner";
  div.innerHTML = `
    <button id="close-image-popup">×</button>
    <a href="https://cutt.ly/thisweektips" target="_blank">
      <img src="https://thisweekpool.com/thisweekpool.gif" alt="Popup Banner">
    </a>
  `;
  document.body.appendChild(div);

  // Close button logic
  div.querySelector("#close-image-popup").addEventListener("click", () => {
    div.style.display = "none";
  });
})();
