import assert from "node:assert/strict";
import { execFile } from "node:child_process";
import { readFile } from "node:fs/promises";
import { fileURLToPath } from "node:url";
import { promisify } from "node:util";

const execFileAsync = promisify(execFile);
const root = new URL("../", import.meta.url);
const example = fileURLToPath(new URL("examples/basic.php", root));

async function main() {
  const expected = JSON.parse(await readText("examples/sample-output.json"));

  if (!(await commandExists("php"))) {
    const exampleSource = await readText("examples/basic.php");
    assert(exampleSource.includes("AdPagesTools::buildUtmUrl"), "example should build a UTM URL");
    assert(exampleSource.includes("AdPagesTools::validateGoogleAdsCopy"), "example should validate ad copy");
    assert(exampleSource.includes("AdPagesTools::localBusinessJsonLd"), "example should generate schema");
    assert(exampleSource.includes("AdPagesTools::landingPageChecklist"), "example should generate checklist");
    console.log("PHP not found; smoke checked static example references only");
    return;
  }

  const run = await execFileAsync("php", [example]);
  const actual = JSON.parse(run.stdout);

  assert.deepEqual(actual, expected, "example output should match checked-in sample output");
  assert(actual.utmUrl.includes("utm_source=google"), "UTM output should include source");
  assert(actual.utmUrl.includes("utm_campaign=emergency-plumber"), "UTM output should include campaign");
  assert.equal(actual.adCopyValid, true, "sample ad copy should pass");
  assert.equal(actual.schemaType, "LocalBusiness", "schema type should be LocalBusiness");
  assert.deepEqual(actual.schemaAreas, ["Perth", "Fremantle"], "schema should include service areas");
  assert.equal(actual.highPriorityFailures, 0, "sample checklist should have no high priority failures");

  console.log("adpages php tools smoke passed");
}

async function readText(file) {
  return readFile(new URL(file, root), "utf8");
}

async function commandExists(command) {
  try {
    await execFileAsync(command, ["--version"]);
    return true;
  } catch {
    return false;
  }
}

main().catch((error) => {
  console.error(error instanceof Error ? error.message : String(error));
  process.exitCode = 1;
});
