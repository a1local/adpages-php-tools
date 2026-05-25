import assert from "node:assert/strict";
import { execFile } from "node:child_process";
import { access, readFile, readdir } from "node:fs/promises";
import { fileURLToPath } from "node:url";
import { promisify } from "node:util";

const execFileAsync = promisify(execFile);
const root = new URL("../", import.meta.url);

const requiredFiles = [
  "composer.json",
  "LICENSE",
  "package.json",
  "README.md",
  "PRIVACY.md",
  "PUBLISH_BLOCKERS.md",
  "src/AdPagesTools.php",
  "src/UtmUrlBuilder.php",
  "src/GoogleAdsCopyValidator.php",
  "src/LocalBusinessJsonLd.php",
  "src/LandingPageChecklist.php",
  "examples/basic.php",
  "examples/sample-output.json",
  "scripts/check.mjs",
  "scripts/smoke.mjs"
];

const phpFiles = [
  "src/AdPagesTools.php",
  "src/UtmUrlBuilder.php",
  "src/GoogleAdsCopyValidator.php",
  "src/LocalBusinessJsonLd.php",
  "src/LandingPageChecklist.php",
  "examples/basic.php"
];

const networkPattern = new RegExp([
  "cur" + "l_",
  "fsock" + "open",
  "stream_socket_" + "client",
  "socket_" + "create",
  "file_get_" + "contents\\s*\\(\\s*['\"]https?:",
  "fopen\\s*\\(\\s*['\"]https?:",
  "wp_remote_" + "(get|post|request)",
  "Guzzle",
  "Http" + "Client",
  "XML" + "HttpRequest",
  "fe" + "tch\\s*\\("
].join("|"), "i");

const credentialPattern = new RegExp([
  "API" + "_KEY",
  "SEC" + "RET",
  "TOK" + "EN",
  "PASS" + "WORD",
  "PRIVATE" + "_KEY"
].join("|"), "i");

async function main() {
  const contents = new Map();
  for (const file of requiredFiles) {
    const content = await readText(file);
    contents.set(file, content);
    assert(content.trim().length > 0, `${file} must not be empty`);
  }

  const composer = JSON.parse(contents.get("composer.json"));
  assert.equal(composer.name, "a1local/adpages-tools");
  assert.equal(composer.type, "library");
  assert.equal(composer.license, "MIT");
  assert.equal(composer.homepage, "https://a1local.com.au/extensions/");
  assert.equal(composer.support.source, "https://github.com/a1local/adpages-php-tools");
  assert.deepEqual(composer.require, { php: ">=8.1" });
  assert.equal(composer.autoload["psr-4"]["AdPages\\Tools\\"], "src/");
  assert(!composer["require-dev"], "composer.json should not require dev packages yet");

  const packageJson = JSON.parse(contents.get("package.json"));
  assert.equal(packageJson.name, "@a1local/php-adpages-tools");
  assert.equal(packageJson.private, true);
  assert.equal(packageJson.scripts.check, "node scripts/check.mjs");
  assert.equal(packageJson.scripts.smoke, "node scripts/smoke.mjs");
  assert(!packageJson.dependencies, "package-local checks must not add dependencies");
  assert(!packageJson.devDependencies, "package-local checks must not add dev dependencies");

  const readme = contents.get("README.md");
  assert(readme.includes("Publishing Position"), "README must include publishing position");
  assert(readme.includes("Publish Blockers"), "README must include publish blockers");
  assert(readme.includes("does not make network calls"), "README must disclose network behavior");

  const privacy = contents.get("PRIVACY.md");
  assert(privacy.includes("does not make network calls"), "PRIVACY must disclose no network calls");
  assert(privacy.includes("does not collect"), "PRIVACY must disclose collection behavior");

  const blockers = contents.get("PUBLISH_BLOCKERS.md");
  assert(blockers.includes("Do not publish"), "publish blockers must be explicit");
  assert(blockers.includes("Packagist"), "publish blockers must mention Packagist readiness");

  const sampleOutput = JSON.parse(contents.get("examples/sample-output.json"));
  assert.equal(sampleOutput.schemaType, "LocalBusiness");
  assert.equal(sampleOutput.adCopyValid, true);
  assert.equal(sampleOutput.checklistScore, 88);

  for (const file of phpFiles) {
    const content = contents.get(file);
    assert(!networkPattern.test(content), `${file} must not include network-call patterns`);
    assert(!credentialPattern.test(content), `${file} must not contain credential placeholder patterns`);
  }

  const sourceFiles = await readdir(new URL("src/", root));
  assert.deepEqual(sourceFiles.filter((file) => file.endsWith(".php")).sort(), [
    "AdPagesTools.php",
    "GoogleAdsCopyValidator.php",
    "LandingPageChecklist.php",
    "LocalBusinessJsonLd.php",
    "UtmUrlBuilder.php"
  ]);

  if (await commandExists("php")) {
    for (const file of phpFiles) {
      await execFileAsync("php", ["-l", fileURLToPath(new URL(file, root))]);
    }
    console.log("PHP lint passed");
  } else {
    console.log("PHP not found; skipped optional php -l checks");
  }

  console.log("adpages php tools checks passed");
}

async function readText(file) {
  return readFile(new URL(file, root), "utf8");
}

async function commandExists(command) {
  try {
    await execFileAsync(command, ["--version"]);
    return true;
  } catch {
    try {
      await access(fileURLToPath(new URL(command, root)));
      return true;
    } catch {
      return false;
    }
  }
}

main().catch((error) => {
  console.error(error instanceof Error ? error.message : String(error));
  process.exitCode = 1;
});
