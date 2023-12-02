import parseSrcset from "srcset-parse";

function isPointWithinImage(
	image: HTMLImageElement,
	mouseX: number,
	mouseY: number,
): boolean {
	const dw = image.clientWidth - image.naturalWidth;
	const dh = image.clientHeight - image.naturalHeight;
	const scale =
		dw < dh
			? image.clientWidth / image.naturalWidth
			: image.clientHeight / image.naturalHeight;

	const cx = image.clientWidth / 2;
	const cy = image.clientHeight / 2;
	const renderedWidth = image.naturalWidth * scale;
	const renderedHeight = image.naturalHeight * scale;

	const boundary = {
		x1: cx - renderedWidth / 2,
		y1: cy - renderedHeight / 2,
		x2: cx + renderedWidth / 2,
		y2: cx + renderedHeight / 2,
	};

	return (
		mouseX < boundary.x1 ||
		mouseY < boundary.y1 ||
		mouseX >= boundary.x2 ||
		mouseY >= boundary.y2
	);
}

class TagPopover {
	private _selectedImageIdx: number | null = null;
	private _imageAlts: Record<string, string> = {};
	private _flipperEls: Record<string, HTMLPictureElement> = {};
	private _images: Array<string> = [];

	private _flipperRoot: HTMLDivElement;
	private _mainImage: HTMLImageElement;

	constructor(private _rootEl: HTMLDivElement) {
		const mainImage =
			this._rootEl.querySelector<HTMLImageElement>(".popover-image img");
		if (!mainImage) {
			throw new Error("Could not find main image");
		}
		this._mainImage = mainImage;
		mainImage.addEventListener("click", this.onImageClick);

		this._setupCloseButton();
		this._setupNavigation();
		this._flipperRoot = this._setupFlipper();
	}

	private _setupNavigation = () => {
		const left = this._rootEl.querySelector<HTMLDivElement>(".popover-left");
		const right = this._rootEl.querySelector<HTMLDivElement>(".popover-right");
		if (!left || !right) {
			throw new Error("Left or right navigation not found");
		}
		left.addEventListener("click", this.previous);
		right.addEventListener("click", this.next);
	};

	private _setupFlipper = () => {
		const flipper =
			this._rootEl.querySelector<HTMLDivElement>(".popover-flipper");
		const pictures =
			flipper?.querySelectorAll<HTMLPictureElement>("picture") ?? [];
		if (!flipper) {
			throw new Error("Flipper not found");
		}
		pictures.forEach(this._setupFlipperImage);

		return flipper;
	};

	private _setupFlipperImage = (picture: HTMLPictureElement) => {
		const img = picture.querySelector("img");
		if (!img) {
			throw new Error("Flipper picture did not contain an img element");
		}
		img.addEventListener("click", () => {
			this.open(img.src);
		});
		this._images.push(img.src);
		this._imageAlts[img.src] = img.alt;
		this._flipperEls[img.src] = picture;
	};

	private _setupCloseButton = () => {
		const close = this._rootEl.querySelector<HTMLDivElement>(".popover-close");
		if (!close) {
			throw new Error("Close button not found");
		}
		close.addEventListener("click", this.close);
	};

	next = () => {
		if (this._selectedImageIdx === null) return;
		this._selectedImageIdx = (this._selectedImageIdx + 1) % this._images.length;
		this.sync();
	};
	previous = () => {
		if (this._selectedImageIdx === null) return;
		this._selectedImageIdx =
			(this._selectedImageIdx - 1 + this._images.length) % this._images.length;
		this.sync();
	};

	open = (url: string) => {
		const imageIdx = this._images.findIndex((src) => src === url);
		if (imageIdx < 0) {
			throw new Error("URL not found!");
		}
		this._selectedImageIdx = imageIdx;
		this.sync();
	};

	close = () => {
		this._selectedImageIdx = null;
		this.sync();
	};

	private onImageClick = (e: MouseEvent) => {
		if (isPointWithinImage(this._mainImage, e.offsetX, e.offsetY)) {
			this.close();
		}
	};

	private onKeyboard = (e: KeyboardEvent) => {
		if (e.code === "Escape") {
			this.close();
			e.preventDefault();
		}
		if (e.code === "ArrowRight") {
			this.next();
			e.preventDefault();
		}
		if (e.code === "ArrowLeft") {
			this.previous();
			e.preventDefault();
		}
	};

	private sync = () => {
		if (this._selectedImageIdx === null) {
			this._rootEl.removeAttribute("data-open");
			document.removeEventListener("keydown", this.onKeyboard);
			return;
		}
		document.addEventListener("keydown", this.onKeyboard);
		const img = this._images[this._selectedImageIdx];
		const el = this._flipperEls[img];
		Object.values(this._flipperEls).forEach((other) => {
			other.removeAttribute("data-active");
		});

		const next = el
			.querySelector("img:not(.frozen)")!
			.cloneNode(true) as HTMLImageElement;
		next.addEventListener("click", this.onImageClick);
		this._mainImage.replaceWith(next);
		this._mainImage = next;

		el.setAttribute("data-active", "");

		el.scrollIntoView({ behavior: "smooth" });

		this._rootEl.setAttribute("data-open", "true");
	};
}

class TagGallery {
	private popover: TagPopover;

	constructor(rootEl: HTMLDivElement) {
		const popoverRoot = rootEl.querySelector<HTMLDivElement>(".tag-popover");
		if (!popoverRoot) throw new Error("No popover found");
		this.popover = new TagPopover(popoverRoot);

		rootEl
			.querySelectorAll<HTMLPictureElement>(".tag-gallery picture")
			.forEach(this._setupPicture);

		rootEl.setAttribute("data-initialized", "");
	}

	private _setupPicture = (picture: HTMLPictureElement) => {
		const img = picture.querySelector("img");
		if (!img) {
			throw new Error("Gallery picture did not contain an img element");
		}

		picture.addEventListener("click", () => {
			this.popover.open(img.src);
		});
	};
}

class HoverGif {
	constructor(private _rootEl: HTMLImageElement) {
		let url = _rootEl.src;
		if (_rootEl.srcset) {
			const candidates = parseSrcset(_rootEl.srcset);
			const bestCandidate = candidates[candidates.length - 1];
			if (bestCandidate) {
				url = bestCandidate.url;
			}
		}

		const urlParsed = new URL(url);
		if (!urlParsed.pathname.endsWith(".gif")) {
			return;
		}
		_rootEl.setAttribute("data-initialized", "");
		_rootEl.src = url;
		_rootEl.addEventListener("load", () => {
			this._setup();
		});
	}

	private _setup = () => {
		const canvas = document.createElement("canvas");
		const width = (canvas.width = this._rootEl.naturalWidth);
		const height = (canvas.height = this._rootEl.naturalHeight);
		canvas.getContext("2d")?.drawImage(this._rootEl, 0, 0, width, height);
		try {
			const frozenUrl = canvas.toDataURL("image/gif");
			const newImg = document.createElement("img");
			newImg.src = frozenUrl;
			newImg.classList.add("frozen");
			newImg.setAttribute("aria-hidden", "true");
			newImg.setAttribute("data-initialized", "");
			this._rootEl.insertAdjacentElement("afterend", newImg);
		} catch (e) {
			// there's nothing we can do
		}
	};
}

document
	.querySelectorAll<HTMLDivElement>(
		".tag-gallery-container:not([data-empty]):not([data-initialized])",
	)
	.forEach((gallery) => new TagGallery(gallery));

document
	.querySelectorAll<HTMLImageElement>(".hover-gif img:not([data-initialized])")
	.forEach((gif) => new HoverGif(gif));
