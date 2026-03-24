import { ResponseBuilder } from "@fermyon/spin-sdk";

export async function handler(_req: Request, res: ResponseBuilder): Promise<void> {
  res.set({ "content-type": "application/json" });
  res.send(JSON.stringify({ ok: true, service: "phyto-ecommerce", ts: new Date().toISOString() }));
}
