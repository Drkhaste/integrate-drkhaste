class Accordion {
  constructor(el) {
    this.el = el;
    this.summary = el.querySelector('summary');
    this.content = el.querySelector('.card-content');

    this.animation = null;
    this.isClosing = false;
    this.isExpanding = false;
    this.summary.addEventListener('click', (e) => this.onClick(e));
  }

  onClick(e) {
    e.preventDefault();
    this.el.style.overflow = 'hidden';
    if (this.isClosing || !this.el.open) {
      this.open();
    } else if (this.isExpanding || this.el.open) {
      this.shrink();
    }
  }

  shrink() {
    this.isClosing = true;

    const startHeight = `${this.el.getBoundingClientRect().height}px`;
    this.el.open = false;
    const endHeight = `${this.el.getBoundingClientRect().height}px`;
    this.el.open = true;

    if (this.animation) {
      this.animation.cancel();
    }

    this.animation = this.el.animate([
      { height: startHeight, opacity: 1 },
      { height: endHeight, opacity: 0.9 }
    ], {
      duration: 350,
      easing: 'cubic-bezier(0.25, 1, 0.5, 1)'
    });

    // Content fade out
    this.content.animate([
      { opacity: 1, transform: 'translateY(0)' },
      { opacity: 0, transform: 'translateY(-10px)' }
    ], {
      duration: 250,
      easing: 'ease-out',
      fill: 'forwards'
    });

    this.animation.onfinish = () => {
      this.content.style.opacity = '';
      this.content.style.transform = '';
      this.onAnimationFinish(false);
    };
    this.animation.oncancel = () => this.isClosing = false;
  }

  open() {
    this.el.style.height = `${this.el.getBoundingClientRect().height}px`;
    this.el.open = true;
    window.requestAnimationFrame(() => this.expand());
  }

  expand() {
    this.isExpanding = true;
    const startHeight = `${this.el.getBoundingClientRect().height}px`;

    this.el.style.height = 'auto';
    const endHeight = `${this.el.getBoundingClientRect().height}px`;
    this.el.style.height = startHeight;

    if (this.animation) {
      this.animation.cancel();
    }

    this.animation = this.el.animate([
      { height: startHeight, opacity: 0.9 },
      { height: endHeight, opacity: 1 }
    ], {
      duration: 400,
      easing: 'cubic-bezier(0.25, 1, 0.5, 1)'
    });

    // Content fade in
    this.content.animate([
      { opacity: 0, transform: 'translateY(-10px)' },
      { opacity: 1, transform: 'translateY(0)' }
    ], {
      duration: 400,
      easing: 'ease-out',
      fill: 'forwards'
    });

    this.animation.onfinish = () => {
      this.content.style.opacity = '';
      this.content.style.transform = '';
      this.onAnimationFinish(true);
    };
    this.animation.oncancel = () => this.isExpanding = false;
  }

  onAnimationFinish(open) {
    this.el.open = open;
    this.animation = null;
    this.isClosing = false;
    this.isExpanding = false;
    this.el.style.height = '';
    this.el.style.overflow = '';
  }
}

document.querySelectorAll('details.section-card').forEach((el) => {
  new Accordion(el);
});
