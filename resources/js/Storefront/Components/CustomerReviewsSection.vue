<script setup>
import { computed, ref } from "vue";
import { Star, Quote, ArrowLeft, ArrowRight } from "@lucide/vue";
const props = defineProps({ reviews: { type: Array, default: () => [] } });
const track = ref(null),
    expanded = ref([]);
const average = computed(() =>
    props.reviews.length
        ? (
              props.reviews.reduce((sum, r) => sum + r.star, 0) /
              props.reviews.length
          ).toFixed(1)
        : "",
);
function move(direction) {
    const el = track.value;
    if (!el) return;
    el.scrollBy({
        left:
            direction *
            (el.querySelector("article")?.getBoundingClientRect().width + 24 ||
                el.clientWidth),
        behavior: window.matchMedia("(prefers-reduced-motion: reduce)").matches
            ? "instant"
            : "smooth",
    });
}
function toggle(id) {
    expanded.value = expanded.value.includes(id)
        ? expanded.value.filter((v) => v !== id)
        : [...expanded.value, id];
}
</script>
<template>
    <section id="customer-reviews" class="customer-stories">
        <div class="stories-inner">
            <div class="stories-heading">
                <div>
                    <p class="stories-eyebrow">THE PEOPLE BEHIND THE STORIES</p>
                    <h2>Words from <em>our customers.</em></h2>
                    <p class="stories-intro">
                        Little moments of joy, shared with us.
                    </p>
                </div>
                <div v-if="reviews.length" class="stories-summary">
                    <span class="average">{{ average }}<small>/ 5</small></span>
                    <div>
                        <div class="summary-stars" aria-hidden="true">
                            <Star
                                v-for="n in 5"
                                :key="n"
                                :size="14"
                                :fill="
                                    n <= Math.round(Number(average))
                                        ? 'currentColor'
                                        : 'none'
                                "
                            />
                        </div>
                        <p>
                            From {{ reviews.length }}
                            {{ reviews.length === 1 ? "review" : "reviews" }}
                        </p>
                    </div>
                </div>
            </div>
            <div
                v-if="reviews.length"
                ref="track"
                class="stories-track"
                tabindex="0"
                aria-label="Customer reviews, scroll horizontally"
            >
                <article
                    v-for="review in reviews"
                    :key="review.id"
                    class="story-card"
                >
                    <div class="story-card-top">
                        <div
                            class="story-stars"
                            role="img"
                            :aria-label="review.star + ' out of 5 stars'"
                        >
                            <Star
                                v-for="n in 5"
                                :key="n"
                                :size="15"
                                :fill="
                                    n <= review.star ? 'currentColor' : 'none'
                                "
                                :class="{ empty: n > review.star }"
                            />
                        </div>
                        <Quote :size="32" stroke-width="1" class="quote-icon" />
                    </div>
                    <blockquote
                        :class="{
                            'story-clamped': !expanded.includes(review.id),
                        }"
                    >
                        {{ review.description }}
                    </blockquote>
                    <button
                        v-if="review.description.length > 180"
                        class="read-review"
                        :aria-expanded="expanded.includes(review.id)"
                        @click="toggle(review.id)"
                    >
                        {{
                            expanded.includes(review.id)
                                ? "Read less"
                                : "Read full review"
                        }}
                    </button>
                    <div class="story-person">
                        <img
                            v-if="review.image"
                            :src="review.image"
                            :alt="review.name"
                            loading="lazy"
                        /><span
                            v-else
                            class="story-initial"
                            aria-hidden="true"
                            >{{ review.name.slice(0, 1) }}</span
                        >
                        <div>
                            <h3>{{ review.name }}</h3>
                            <span>Customer story</span>
                        </div>
                    </div>
                </article>
            </div>
            <div v-else class="stories-empty">
                <Quote :size="32" />
                <p>Every experience has a story.</p>
                <span>Customer reviews will appear here soon.</span>
            </div>
            <div v-if="reviews.length > 1" class="stories-controls">
                <span>More stories, more inspiration</span>
                <div>
                    <button aria-label="Previous reviews" @click="move(-1)">
                        <ArrowLeft :size="19" /></button
                    ><button aria-label="Next reviews" @click="move(1)">
                        <ArrowRight :size="19" />
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
<style scoped>
.customer-stories {
    background: #F3F6FB;
    padding: 80px 0;
    scroll-margin-top: 180px;
}
.stories-inner {
    max-width: 1280px;
    margin: auto;
    padding: 0 24px;
}
.stories-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 30px;
    margin-bottom: 38px;
}
.stories-eyebrow {
    font-size: 10px;
    letter-spacing: 0.24em;
    font-weight: 600;
    color: #64748B;
    margin-bottom: 15px;
}
.stories-heading h2 {
    font-family: Poppins, sans-serif;
    font-size: 46px;
    line-height: 1.08;
    font-weight: 500;
}
.stories-heading h2 em {
    color: #E30613;
}
.stories-intro {
    font-size: 12px;
    color: #64748B;
    margin-top: 15px;
}
.stories-summary {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-shrink: 0;
}
.average {
    font-family: Poppins, sans-serif;
    font-size: 38px;
}
.average small {
    font-family: Poppins, sans-serif;
    font-size: 12px;
    color: #64748B;
    margin-left: 5px;
}
.summary-stars,
.story-stars {
    display: flex;
    gap: 4px;
    color: #64748B;
}
.stories-summary p {
    font-size: 10px;
    color: #64748B;
    margin-top: 6px;
}
.stories-track {
    display: flex;
    gap: 24px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    padding: 3px 2px 18px;
    scrollbar-width: thin;
    scrollbar-color: #DCE4EF transparent;
}
.story-card {
    flex: 0 0 calc((100% - 48px) / 3);
    scroll-snap-align: start;
    background: #fff;
    border: 1px solid #F3F6FB;
    border-radius: 3px;
    padding: 28px;
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.story-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
}
.quote-icon {
    color: #DCE4EF;
}
.story-stars .empty {
    color: #DCE4EF;
}
.story-card blockquote {
    font-size: 14px;
    line-height: 1.9;
    color: #0B2A5B;
    white-space: pre-line;
    overflow-wrap: anywhere;
}
.story-clamped {
    display: -webkit-box;
    -webkit-line-clamp: 5;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.read-review {
    font-size: 11px;
    color: #E30613;
    text-decoration: underline;
    text-underline-offset: 4px;
    align-self: flex-start;
    margin-top: 12px;
    background: none;
    border: 0;
}
.story-person {
    display: flex;
    align-items: center;
    gap: 13px;
    border-top: 1px solid #F3F6FB;
    padding-top: 22px;
    margin-top: auto;
}
.story-card blockquote + .story-person,
.read-review + .story-person {
    margin-top: 28px;
}
.story-person img,
.story-initial {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.story-initial {
    display: grid;
    place-items: center;
    background: #F3F6FB;
    color: #64748B;
    font-family: Poppins, sans-serif;
    font-size: 24px;
}
.story-person h3 {
    font-size: 12px;
    font-weight: 600;
    overflow-wrap: anywhere;
}
.story-person span:not(.story-initial) {
    font-size: 9px;
    letter-spacing: 0.08em;
    color: #64748B;
    display: block;
    margin-top: 4px;
}
.stories-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 22px;
}
.stories-controls > span {
    font-size: 10px;
    letter-spacing: 0.07em;
    color: #64748B;
}
.stories-controls > div {
    display: flex;
    gap: 9px;
}
.stories-controls button {
    width: 42px;
    height: 42px;
    border: 1px solid #DCE4EF;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: transparent;
    transition: background 0.2s;
}
.stories-controls button:hover {
    background: #DCE4EF;
}
.stories-empty {
    text-align: center;
    padding: 32px;
    border: 1px dashed #DCE4EF;
    border-radius: 8px;
    color: #64748B;
}
.stories-empty svg {
    margin: auto;
}
.stories-empty p {
    font-family: Poppins, sans-serif;
    font-size: 22px;
    margin: 12px;
}
.stories-empty span {
    font-size: 12px;
}
@media (max-width: 1000px) {
    .story-card {
        flex-basis: calc((100% - 24px) / 2);
    }
    .stories-heading h2 {
        font-size: 38px;
    }
}
@media (max-width: 640px) {
    .customer-stories {
        padding: 48px 0;
    }
    .stories-inner {
        padding: 0 20px;
    }
    .stories-heading {
        display: block;
        margin-bottom: 26px;
    }
    .stories-heading h2 {
        font-size: 35px;
        max-width: 300px;
    }
    .stories-summary {
        margin-top: 22px;
    }
    .average {
        font-size: 28px;
    }
    .story-card {
        flex-basis: 90%;
        padding: 24px;
    }
    .stories-track {
        gap: 16px;
    }
    .stories-controls > span {
        max-width: 160px;
        line-height: 1.7;
    }
}
</style>
