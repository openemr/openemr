# eCQM measure bundle builder: cqm-parsers (Ruby) turns CMS eCQM packages
# into the measure and value set JSON OpenEMR calculates from.
FROM ruby:3.3

WORKDIR /builder

COPY Gemfile Gemfile.lock ./
RUN gem install bundler -v 2.2.14 && bundle install

COPY build_bundle.rb generate_ecqm_list.rb ./

# Measure packages and the generated bundles live in the mounted /bundles.
ENV BUNDLE_DIR=/bundles
CMD ["ruby", "build_bundle.rb", "--help"]
